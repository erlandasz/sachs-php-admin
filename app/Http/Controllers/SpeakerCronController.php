<?php

namespace App\Http\Controllers;

use Airtable;
use App\Jobs\ProcessSpeakerJob;
use App\Models\Event;
use App\Services\AirtableService;
use App\Services\DynamicConfigService;

class SpeakerCronController extends Controller
{
    protected $airtableService;

    public function __construct(AirtableService $airtableService)
    {
        $this->airtableService = $airtableService;
    }

    public function index()
    {
        set_time_limit(300);
        logger()->info('speakers upload started...');
        $failed_events = [];

        $events = Event::where('show_event', true)->get()->all();

        foreach ($events as $event) {
            $base_id = $event->airtable_base;
            $event_slug = $event->slug;
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
                logger()->debug('Dispatching job for speaker ...');
                ProcessSpeakerJob::dispatch($record, $this->airtableService);
            }
        }

        return response()->json();
    }
}
