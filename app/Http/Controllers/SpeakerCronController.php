<?php

namespace App\Http\Controllers;

use Airtable;
use App\Jobs\ProcessSpeakerJob;
use App\Models\Event;
use App\Models\Person;
use App\Services\AirtableService;
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

class SpeakerCronController extends Controller
{
    public function __construct(AirtableService $airtableService)
    {
        $this->airtableService = $airtableService;
    }

    public function index()
    {
        set_time_limit(300);
        \Log::info('speakers upload started...');
        $failed_events = [];

        $events = Event::where('show_event', true)->get()->all();

        $results = [];
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

            $filteredRecords = collect($records)
                ->filter(function ($record) use ($event) {
                    $events_attended = $record['fields']['eventAttendingFromPanel'] ?? '';
                    $parts = explode('-', $event->slug);
                    $secondPart = strtoupper($parts[1]);

                    return stripos($events_attended, $secondPart) !== false;
                })
                ->filter(function ($record) {
                    $role = $record['fields']['Event Role'] ?? '';
                    $keywords = ['Chair', 'Speaker', 'Moderator'];
                    if (is_array($role)) {
                        $role = implode(' ', $role); // Join array values into a string
                    }

                    $hasRole = false;
                    foreach ($keywords as $keyword) {
                        if (stripos($role, $keyword) !== false) {
                            $hasRole = true;
                            break;
                        }
                    }

                    return $hasRole;
                });

            foreach ($filteredRecords as $record) {
                \Log::debug('Dispatching job for speaker ...');
                ProcessSpeakerJob::dispatch($record, $this->airtableService);
            }
        }

        return response()->json();
    }

    private function updatePerson($id, $fn, $ln, $fulln, $comp, $jt, $rec_id)
    {
        $person = Person::findOrFail($id);
        $person->first_name = $fn;
        $person->last_name = $ln;
        $person->full_name = $fulln;
        $person->companyName = $comp;
        $person->job_title = $jt;
        $person->airtableId = $rec_id;

        $person->save();

    }

    private function createPerson($fn, $ln, $fulln, $comp, $jt, $rec_id)
    {
        $person = Person::create([
            'first_name' => $fn,
            'last_name' => $ln,
            'full_name' => $fulln,
            'companyName' => $comp,
            'job_title' => $jt,
            'airtableId' => $rec_id,
            'title' => '-',
            'bio' => '-',
        ]);

        if (isset($rec_id)) {
            $this->airtableService->loadSpeaker($person);
        }

        return $person;
    }
}
