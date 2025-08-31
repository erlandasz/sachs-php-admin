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
use Illuminate\Support\Facades\Log;

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
        $events = Event::where('show_event', true)->get();

        Log::info('Starting presenter processing for '.$events->count().' events.');

        foreach ($events as $event) {
            $base_id = $event->airtable_base;
            $event_slug = $event->slug;
            $created = 0;
            $updated = 0;

            if (! $base_id) {
                Log::error('No Airtable base set for event: '.$event_slug);
                $failed_events[] = $event_slug.' has no base set';

                continue;
            }

            DynamicConfigService::setDynamicConfig($event_slug, $base_id);

            try {
                $records = Airtable::table($base_id)->where('Status', 'Confirmed')->all();
            } catch (\Exception $e) {
                Log::error('Cannot access Airtable table for event: '.$event_slug.'. Exception: '.$e->getMessage());
                $failed_events[] = $event_slug.' cannot access Airtable table';

                continue;
            }
            $matchedRoles = collect();

            $filteredRecords = collect($records)
                ->filter(function ($record) use (&$matchedRoles) {
                    $roles = $record['fields']['Presentation/Showcase'] ?? [];
                    Log::warning($roles);
                    foreach ($roles as $role) {
                        $matchedRoles->push($role);
                    }
                    if (! is_array($roles)) {
                        if (isset($roles)) {
                            Log::error('roles not array', [
                                'roles' => $roles,
                            ]);
                        }

                        $roles = [$roles];
                    }
                    $required_roles = ['10-min In-Person', '20-min In-Person', '5-min Showcase', '10-min Showcase'];

                    foreach ($required_roles as $required_role) {
                        if (in_array($required_role, $roles)) {
                            // Record matches required role
                            return true;
                        }
                    }

                    return false;
                })
                ->filter(function ($record) use ($event) {
                    $parts = explode('-', $event->slug);
                    $secondPart = strtoupper($parts[1] ?? '');
                    $roles = $record['fields']['Presentation/Showcase'] ?? [];

                    if (! is_array($roles)) {
                        $roles = [$roles];
                    }

                    foreach ($roles as $role) {
                        if (stripos(strtoupper($role), $secondPart) !== false) {
                            return true;
                        }
                    }

                    return false;
                })
                ->values();
            Log::info('All roles: '.$matchedRoles);

            Log::info('Event '.$event_slug.' - Found '.count($filteredRecords).' relevant records.', ['matched_roles' => $matchedRoles]);

            foreach ($filteredRecords as $record) {
                $company_name = $record['fields']['Company Name'] ?? '';
                $record_id = $record['fields']['record_id'] ?? null;
                $role = $this->extractRoleFromRecord($record);

                if (is_array($record_id)) {
                    if (count($record_id) === 1) {
                        $record_id = $record_id[0];
                    } else {
                        $record_id = json_encode($record_id);
                    }
                }

                if (empty($company_name)) {
                    Log::warning('Skipping record with empty company name.', ['record' => $record]);

                    continue;
                }

                $existing = Company::where('name', $company_name)->first();

                if ($existing) {
                    $needs_update = Carbon::parse($existing->updated_at)->lt(Carbon::now()->subDays(2)) || ! $existing->airtableId;
                    if ($needs_update) {
                        $this->update_company($existing, $record_id, $role, $event);
                        $updated++;
                    } else {
                        Log::debug('Company up-to-date, skipping update: '.$company_name);
                    }
                } else {
                    $this->create_company($company_name, $record_id, $role, $event);
                    $created++;
                }
            }

            Log::info('Event '.$event_slug.' - Finished processing. Updated: '.$updated.', Created: '.$created.', Total matched: '.count($filteredRecords));
        }

        if (! empty($failed_events)) {
            Log::warning('Some events failed processing.', ['failed_events' => $failed_events]);
        }
    }

    private function extractRoleFromRecord($record): ?string
    {
        $roles = $record['fields']['Presentation/Showcase'] ?? [];
        if (! is_array($roles)) {
            $roles = [$roles];
        }
        $required_roles = ['10-min In-Person', '20-min In-Person', '5-min Showcase', '10-min Showcase'];

        foreach ($required_roles as $required_role) {
            if (in_array($required_role, $roles)) {
                return $required_role;
            }
        }

        return null;
    }

    private function create_company(string $company_name, $record_id, ?string $role, Event $event): void
    {
        Log::info('Creating company: '.$company_name);

        $company = Company::create([
            'name' => $company_name,
            'airtableId' => $record_id,
        ]);

        $this->addPresentationToEvent($company->id, $role, $event->id);
    }

    private function update_company(Company $existing_company, $record_id, ?string $role, Event $event): void
    {
        Log::info('Updating company: '.$existing_company->name);

        $existing_company->airtableId = $record_id;
        $existing_company->save();

        $this->addPresentationToEvent($existing_company->id, $role, $event->id);
    }

    private function addPresentationToEvent(int $companyId, ?string $role, int $event_id): void
    {
        if (empty($role)) {
            Log::debug('No role specified, skipping presentation creation for company ID '.$companyId);

            return;
        }

        $existing_role = PresenterType::whereRaw(
            'LOWER(REPLACE(REPLACE(name, "-", " "), "min", "minute")) = ?',
            [$this->normalizeRole($role)]
        )->first();

        if (! isset($existing_role)) {
            Log::debug('Role not found in PresenterType: '.$role);

            return;
        }

        $existing_presentation = EventPresenter::where('company_id', $companyId)
            ->where('event_id', $event_id)
            ->where('presenter_type_id', $existing_role->id)
            ->first();

        if ($existing_presentation) {
            Log::debug('Presentation already exists for company ID '.$companyId.' event ID '.$event_id.' role ID '.$existing_role->id);

            return;
        }

        $result = EventPresenter::create([
            'event_id' => $event_id,
            'company_id' => $companyId,
            'presenter_type_id' => $existing_role->id,
        ]);

        Log::info('Presentation created.', ['presentation_id' => $result->id ?? null]);
    }

    private function normalizeRole(string $string): string
    {
        $string = strtolower($string);
        $string = str_replace(['min', '-'], 'minute ', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        $string = trim($string);

        return $string;
    }
}
