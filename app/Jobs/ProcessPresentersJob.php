<?php

namespace App\Jobs;

use Airtable;
use App\Models\Company;
use App\Models\Event;
use App\Models\EventPresenter;
use App\Models\PresenterType;
use App\Services\DynamicConfigService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPresentersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $failed_events = [];
        $events = Event::where('show_event', true)->get()->all();

        foreach ($events as $event) {
            $base_id = $event->airtable_base;
            $event_slug = $event->slug;
            $created = 0;
            $updated = 0;

            if (! $base_id) {
                logger()->error('No airtable base! '.$event_slug);

                $failed_events[] = $event_slug.' '.'has no base set';

                continue;
            }
            DynamicConfigService::setDynamicConfig($event->slug, $base_id);

            try {

                $records = Airtable::table($base_id)->where('Status', 'Confirmed')->all();

            } catch (\Exception $e) {
                logger()->error('Cant access table for '.$event_slug);
                $failed_events[] = $event_slug.' '.'cant access table';

                continue;
            }

            $role = null;

            collect($records)
                ->filter(function ($record) {
                    $roles = $record['fields']['Presentation/Showcase'] ?? [];

                    if (! is_array($roles)) {
                        $roles = [$roles];
                    }

                    $required_roles = ['10-min In-Person', '20-min In-Person', '5-min Showcase', '10-min Showcase'];

                    foreach ($required_roles as $required_role) {
                        if (in_array($required_role, $roles)) {
                            $role = $required_role;

                            return true;
                        }
                    }

                    return false;
                })
                ->filter(function ($record) use ($event) {
                    $parts = explode('-', $event->slug);
                    $secondPart = strtoupper($parts[1]);

                    $roles = $record['fields']['Presentation/Showcase'] ?? [];
                    if (! is_array($roles)) {
                        $roles = [$roles];
                    }

                    foreach ($roles as $role) {
                        if (stripos(strtoupper($role), $secondPart) !== false) {
                            return true; // this record matches
                        }
                    }

                    return false; // no matching role found
                })->values()->map(function ($record) use ($updated, $created, $role, $event) {
                    $company_name = $record['fields']['Company Name'] ?? '';

                    $record_id = $record['fields']['record_id'] ?? null;

                    if (is_array($record_id)) {
                        // If it's an array with one item, extract that item as string
                        if (count($record_id) === 1) {
                            $record_id = $record_id[0];
                        } else {
                            // Convert array to string representation if needed, or handle error
                            $record_id = json_encode($record_id);
                        }
                    }

                    $existing = Company::where('name', $company_name)->first();

                    if ($existing) {
                        if (Carbon::parse($existing->updated_at)->lt(Carbon::now()->subDays(2)) || ! $existing->airtableId) {
                            $this->update_company($existing->id, $record_id, $role, $event);
                            $updated++;
                        }
                    } else {

                        $this->create_company($company_name, $record_id, $role, $event);
                        $created++;
                    }
                });

            logger()->debug('updated: '.$updated.' created'.$created);
        }
    }

    private function create_company($company_name, $record_id, $role, $event)
    {
        logger()->info('Processing create '.$company_name);
        $company = Company::create([
            'name' => $company_name,
            'airtableId' => $record_id,
        ]);

        $this->addPresentationToEvent($company->id, $role, $event->id);
    }

    private function update_company($existing_company, $record_id, $role, $event)
    {
        logger()->info('Processing update '.$existing_company->name);

        $existing_company->airtableId = $record_id;
        $existing_company->save();

        $this->addPresentationToEvent($existing_company->id, $role, $event->id);
    }

    private function addPresentationToEvent($id, $role, $event_id)
    {
        function normalizeRole($string)
        {
            $string = strtolower($string);                                  // lowercase
            $string = str_replace(['min', '-'], 'minute ', $string);        // replace 'min' and dashes
            $string = preg_replace('/\s+/', ' ', $string);                  // replace multiple spaces with one
            $string = trim($string);                                        // trim whitespace

            return $string;
        }
        $existing_role = PresenterType::whereRaw('LOWER(REPLACE(REPLACE(name, "-", " "), "min", "minute")) = ?', [normalizeRole($role)])->first();

        $existing_presentation = EventPresenter::where('company_id', $id)->andWhere('event_id', $event_id)->andWhere('presenter_type_id', $existing_role->id)->first();

        if (! isset($existing_role)) {
            logger()->debug('Role not found '.$role);

            return;
        }

        if (isset($existing_presentation)) {
            logger()->debug('Pr already exists');

            return;
        }

        $result = EventPresenter::create([
            'event_id' => $event_id,
            'company_id' => $id,
            'presenter_type_id' => $existing_role->id,
        ]);

        logger()->info('Presentation created. ', [
            'result' => $result,
        ]);
    }
}
