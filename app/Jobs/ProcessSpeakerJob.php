<?php

namespace App\Jobs;

use App\Models\Person;
use App\Services\AirtableService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSpeakerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record;

    protected $airtableService;

    public function __construct($record, AirtableService $airtableService)
    {
        $this->record = $record;
        $this->airtableService = $airtableService;
    }

    public function handle()
    {

        $full_name = $this->record['fields']['Full Name'] ?? '';
        $existing = Person::where('full_name', $full_name)->first();
        $first_name = $this->record['fields']['firstname'] ?? null;
        $last_name = $this->record['fields']['lastname'] ?? null;
        $company = $this->record['fields']['Company Name'] ?? null;
        $job_title = $this->record['fields']['jobtitle'] ?? null;
        $record_id = $this->record['fields']['record_id'] ?? null;
        logger()->debug('Processing '.$full_name);
        if (is_array($record_id)) {
            if (count($record_id) === 1) {
                $record_id = $record_id[0];
            } else {
                $record_id = json_encode($record_id);
            }
        }

        if ($existing) {
            if (Carbon::parse($existing->updated_at)->lt(Carbon::now()->subDays(2))) {
                $this->updatePerson($existing->id, $first_name, $last_name, $full_name, $company, $job_title, $record_id);
            }
        } else {
            $this->createPerson($first_name, $last_name, $full_name, $company, $job_title, $record_id);
        }
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
        if ($rec_id) {
            $this->airtableService->loadSpeaker($person);
        }

        return $person;
    }
}
