<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AirtableService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Storage;

class CronController extends Controller
{
    protected AirtableService $airtableService;

    protected function __construct(AirtableService $airtableService)
    {
        $this->airtableService = $$airtableService;
    }

    public function attendees(): JsonResponse
    {
        \Log::info('attendees upload started...');

        $events = Event::where('show_event', true)->get()->all();
        $results = [];
        foreach ($events as $event) {
            $base_id = $event->airtable_base;
            $event_slug = $event->slug;

            if (! $base_id) {
                \Log::error('No airtable base! '.$event_slug);

                continue;
            }

            try {
                $records = Airtable->table($base_id)->where('Status', 'Confirmed')->all();

            } catch (\Exception $e) {
                \Log::error('Cant access table for '.$event_slug);

                continue;
            }

            $allCompanies = collect($records)
                ->filter(function ($record) {
                    $companyName = $record['fields']['Company Name'] ?? '';

                    return strpos($companyName, 'Independent') === false;
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
                ->unique(function ($record) {
                    return $record['fields']['Company Name'];
                })
                ->values()
                ->pluck('fields.Company Name')
                ->all();

            // Save attendees CSV
            $attendeesFilename = $eventSlug.'-attendees.csv';
            $attendeesPath = 'attendees/'.$eventSlug.'/'.$attendeesFilename;
            $csv = fopen('php://temp', 'w');
            foreach ($allCompanies as $company) {
                fputcsv($csv, [$company]);
            }
            rewind($csv);
            Storage::disk('r2')->put($attendeesPath, stream_get_contents($csv));
            fclose($csv);

            $investorsFilename = $eventSlug.'-investors.csv';
            $investorsPath = 'attendees/'.$eventSlug.'/'.$investorsFilename;
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
        ]);
    }
}
