<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;

function setAirtableConfigDynamic($tableName, $baseId)
{
    // Get current tables config
    $tables = Config::get('airtable.tables', []);

    // Add or overwrite a table config dynamically
    $tables[$tableName] = [
        'name' => 'Registrations', // or any other friendly name you want for the table
        'base' => $baseId,
    ];

    // Set the updated tables config
    Config::set('airtable.tables', $tables);
}

class PresenterCron extends Controller
{
    public function index()
    {
        set_time_limit(300);
        $tables = Config::get('airtable.tables', []);

        $failed_events = [];
        $results = [];
        $events = Event::where('show_event', true)->get()->all();

        foreach ($events as $event) {
            $base_id = $event->airtable_base;
            $event_slug = $event->slug;
            $created = 0;
            $updated = 0;

            if (! $base_id) {
                \Log::error('No airtable base! '.$event_slug);

                $failed_events[] = $event_slug.' '.'has no base set';

                continue;
            }
            setAirtableConfigDynamic($event->slug, $base_id);

            try {
                $records = Airtable::table($base_id)->where('Status', 'Confirmed')->all();

            } catch (\Exception $e) {
                \Log::error('Cant access table for '.$event_slug);
                $failed_events[] = $event_slug.' '.'cant access table';

                continue;
            }

            $all_presenters = collect($records)
                ->filter(function ($record) {
                    $roles = $record['fields']['Presentation/Showcase'] ?? [];

                    if (! is_array($roles)) {
                        $roles = [$roles];
                    }

                    $required_roles = ['10-min In-Person', '20-min In-Person', '5-min Showcase', '10-min Showcase'];

                    foreach ($required_roles as $required_role) {
                        if (in_array($required_role, $roles)) {
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
                })->values()->map(function ($record) {
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
                            $this->update_company($existing->id, $record_id);
                            $updated++;
                        }
                    } else {

                        $this->create_company($company_name, $record_id);
                        $created++;
                    }
                });
        }

    }

    private function create_company($company_name, $record_id)
    {
        Company::create([
            'name' => $company_name,
            'airtableId' => $record_id,
        ]);
    }

    private function update_company($existing_company, $record_id)
    {
        $existing_company->airtableId = $record_id;
        $existing_company->save();
    }
}
