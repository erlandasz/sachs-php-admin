<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AirtableService;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Storage;
use Airtable;

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

class CronController extends Controller
{
    protected AirtableService $airtableService;

    public function __construct(AirtableService $airtableService)
    {
        $this->airtableService = $airtableService;
    }

    public function attendees(): JsonResponse
    {
        \Log::info('attendees upload started...');

        $failed_events = [];

        $events = Event::where('show_event', true)->get()->all();
        $results = [];
        foreach ($events as $event) {
            $base_id = $event->airtable_base;
            $event_slug = $event->slug;

            setAirtableConfigDynamic($event->slug, $base_id);

            if (! $base_id) {
                \Log::error('No airtable base! '.$event_slug);

                $failed_events[] = $event_slug. ' '. 'has no base set';

                continue;
            }

            try {
                $records = Airtable::table($base_id)->where('Status', 'Confirmed')->all();

            } catch (\Exception $e) {
                \Log::error('Cant access table for '.$event_slug);
                $failed_events[] = $event_slug. ' '. 'cant access table';

                continue;
            }

            $allCompanies = collect($records)
                ->filter(function ($record) {
                    $companyName = $record['fields']['Company Name'] ?? '';

                    return strpos($companyName, 'Independent') === false;
                })
                ->filter(function ($record)  use ($event) {
                    $events_attending = $record['fields']['Event Attending'] ?? '';

                    $parts = explode('-', $event->slug);
                    $secondPart = strtoupper($parts[1]);

                    return stripos($events_attending, $secondPart) !== false || stripos($events_attending, 'Virtual Week') !== false;
                })
                ->unique(function ($record) {
                    return $record['fields']['Company Name'];
                })
                ->values()
                ->pluck('fields.Company Name')
                ->all();

            $investorCompanies = collect($records)
                ->filter(function ($record) {
                    $companyName = $record['fields']['Company Name'] ?? '';

                    return strpos($companyName, 'Independent') === false;
                })
                ->filter(function ($record) {
                    return isset($record['fields']['Should be listed under Investors Attending?'])
                        && $record['fields']['Should be listed under Investors Attending?'] === 'Yes';
                })
                ->filter(function ($record)  use ($event) {
                    $events_attending = $record['fields']['Event Attending'] ?? '';

                    $parts = explode('-', $event->slug);
                    $secondPart = strtoupper($parts[1]);

                    return stripos($events_attending, $secondPart) !== false || stripos($events_attending, 'Virtual Week') !== false;
                })
                ->unique(function ($record) {
                    return $record['fields']['Company Name'];
                })
                ->values()
                ->pluck('fields.Company Name')
                ->all();

            // Save attendees CSV
            $attendeesFilename = $event_slug.'-attendees.csv';
            $attendeesPath = 'attendees/'.$event_slug.'/'.$attendeesFilename;
            $csv = fopen('php://temp', 'w');
            foreach ($allCompanies as $company) {
                fputcsv($csv, [$company]);
            }
            rewind($csv);
            Storage::disk('r2')->put($attendeesPath, stream_get_contents($csv));
            fclose($csv);

            $investorsFilename = $event_slug.'-investors.csv';
            $investorsPath = 'attendees/'.$event_slug.'/'.$investorsFilename;
            $csv = fopen('php://temp', 'w');
            foreach ($investorCompanies as $company) {
                fputcsv($csv, [$company]);
            }
            rewind($csv);
            Storage::disk('r2')->put($investorsPath, stream_get_contents($csv));
            fclose($csv);

            $event->attendees_updated = Carbon::now()->format('Y-m-d');
            $event->save();

            $results[$event_slug] = [
                'investors' => count($investorCompanies),
                'all_records' => $allCompanies,
            ];
        }

        \Log::info('attendees upload finished...');

        return response()->json([
            'message' => 'Attendees and Investors uploaded successfully',
            'data' => $results,
            'erorrs' => $failed_events
        ]);
    }
}
