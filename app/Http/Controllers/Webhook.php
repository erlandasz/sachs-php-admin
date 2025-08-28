<?php

namespace App\Http\Controllers;

use Airtable;
use App\Models\Company;
use App\Models\Person;
use Illuminate\Http\Request;

class Webhook extends Controller
{
    public function airtableWebhook(Request $request)
    {
        $recordId = trim($request->input('record_id'));
        $companyName = trim($request->input('companyName'));
        $firstName = trim($request->input('first_name'));
        $lastName = trim($request->input('last_name'));
        $profileTypes = explode(',', $request->input('profile_type', ''));
        $speaker_response = null;
        $presenter_response = null;

        $isSpeaker = in_array('speaker', $profileTypes);
        $isPresenter = in_array('presenter', $profileTypes);

        if ($isSpeaker) {
            $speaker_response = $this->processSpeaker($firstName, $lastName, $companyName, $recordId);
        }

        if ($isPresenter) {
            $presenter_response = $this->processPresenter($companyName, $recordId);
        }

        return response()->json([
            'speaker_response' => $speaker_response,
            'presenter_response' => $presenter_response,
        ]);
    }

    private function normalizeName(string $name): string
    {
        return trim(preg_replace('/\b(ltd|inc|corp|gmbh|llc)\b/i', '', strtolower($name)));
    }

    public function loadCompany(string $id)
    {
        $company = Company::findOrFail($id);

        if (empty($company->airtableId)) {
            throw new \Exception('Cannot fetch a company without airtableId');
        }

        $airtableEntryFields = $this->getEntry($company->airtableId);

        if (isset($airtableEntryFields['High Resolution Company Logo'])) {
            $image = $airtableEntryFields['High Resolution Company Logo'];
        } else {
            $image = null;
        }

        if (isset($image) && isset($image[0]) && isset($image[0]['url'])) {
            $imageName = $this->extractLogo($image[0]['url']);

            $company->logo_name = $imageName ?? $this->defaultImageName;
            $company->cloudinary_url = null;
        }

        foreach ($this->airtableFieldMap as $property => $fieldName) {
            $company->$property = ! empty($airtableEntryFields[$fieldName]) ? str_replace("\n\n", "\n", $airtableEntryFields[$fieldName]) : $company->$property;
        }

        return $company->save();
    }

    private function extractLogo(string $imageLink)
    {
        $imageContent = @file_get_contents($imageLink);

        return $this->imageService->createCompanyImg($imageContent, 420, 210, 'images/compLogos/');
    }

    private function processSpeaker(string $firstName, string $lastName, string $companyName, string $recordId)
    {
        $closestSpeaker = Person::where('first_name', trim($firstName))
            ->where('last_name', trim($lastName))
            ->first();

        $updated = false;
        $created = false;

        if ($closestSpeaker) {
            $closestSpeaker->airtableId = $recordId;
            $closestSpeaker->save();
            $this->loadFromAirtable($closestSpeaker->id);
            $updated = true;
        } else {
            $closestSpeaker = Person::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'company_name' => $companyName,
                'airtableId' => $recordId,
                'job_title' => '-',
                'bio' => '-',
                'title' => 'dr',
            ]);
            $this->loadFromAirtable($closestSpeaker->id);
            $created = true;
        }

        return ['created' => $created, 'updated' => $updated, 'id' => $closestSpeaker->id];
    }

    private function processPresenter(string $companyName, string $recordId)
    {
        $companies = Company::all();
        $closestCompany = null;
        $minDistance = 3;

        $created = false;
        $updated = false;
        $companyId = null;

        $normalizedCompanyName = $this->normalizeName($companyName);

        foreach ($companies as $company) {
            $normalizedDbName = $this->normalizeName($company->name);

            if (str_contains($normalizedDbName, $normalizedCompanyName) || str_contains($normalizedCompanyName, $normalizedDbName)) {
                $companyId = $company->id;
                break; // Found a close enough match
            }

            $distance = levenshtein($normalizedDbName, $normalizedCompanyName);
            if ($distance < $minDistance) {
                $companyId = $company->id;
                $minDistance = $distance;
            }
        }

        if ($companyId) {
            Company::where('id', $companyId)->update(['airtableId' => $recordId]);
            $this->loadCompany($companyId);
            $updated = true;
        } else {
            $closestCompany = Company::create([
                'name' => $companyName,
                'airtableId' => $recordId,
                'about' => '-',
            ]);
            $companyId = $closestCompany->id;
            $this->loadCompany($closestCompany->id);
            $created = true;
        }

        return ['created' => $created, 'updated' => $updated, 'id' => $companyId];
    }

    public function getEntry(string $airtableId)
    {
        $entry = Airtable::table($this->table)->find($airtableId);

        if (! $entry) {
            throw new Exception('Airtable entry not found', $airtableId);
        }

        return $entry['fields'];
    }
}
