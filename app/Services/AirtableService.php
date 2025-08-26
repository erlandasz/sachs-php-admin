<?php

namespace App\Services;

use Airtable;
use App\Models\Company;
use App\Models\Person;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AirtableService
{
    protected $table = 'company-profiles';

    protected $defaultImageName = 'noPic.webp';

    protected $imageService;

    private $airtableFieldMap = [
        'name' => 'Company Name',
        'profile' => 'Company Profile',
        'sector' => 'Company Sector',
        'type' => 'Company Type',
        'website' => 'Company Website',
        'founded' => 'Year Founded',
        'city' => 'City',
        'state' => 'State/Province',
        'zip' => 'Postal / Zip Code',
        'country' => 'Country',
        'short_profile' => 'Short Company Profile',
        'financial_summary' => 'Financial Summary',
        'phone_no' => 'Switchboard/HQ Phone Number',
        'email' => 'Contact Email',
    ];

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function getEntry(string $airtableId)
    {
        $entry = Airtable::table($this->table)->find($airtableId);

        if (! $entry) {
            throw new Exception('Airtable entry not found', $airtableId);
        }

        return $entry['fields'];
    }

    public function getPayingCustomersByStatus(string $table, string $status)
    {
        $stuff = Airtable::table($table)->where('On Portal?', $status)->get();

        return $stuff;
    }

    public function changePayingCustomerStatus(string $airtableId, string $table, string $status)
    {
        Airtable::table($table)->patch($airtableId, ['On Portal?' => $status]);
    }

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

        // Normalize input name
        $normalizedCompanyName = $this->normalizeName($companyName);

        foreach ($companies as $company) {
            $normalizedDbName = $this->normalizeName($company->name);

            // Prioritize substring matches
            if (str_contains($normalizedDbName, $normalizedCompanyName) || str_contains($normalizedCompanyName, $normalizedDbName)) {
                $companyId = $company->id;
                break; // Found a close enough match
            }

            // Levenshtein distance for fuzzy matching
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

    // Normalize function: removes common suffixes and trims whitespace
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
        try {
            $imageContent = file_get_contents($imageLink);
            if ($imageContent === false) {
                throw new \Exception("Failed to download image from {$imageLink}");
            }
        } catch (\Exception $e) {
            $imageContent = null;

            return;
        }

        return $this->imageService->createCompanyImg($imageContent, 420, 210, 'images/compLogos/');
    }

    public function loadSpeaker(Person $person)
    {
        $airtableId = $person->airtableId;

        $fields = [
            'bio' => 'Biography',
            'job_title' => 'Job Title',
            'companyName' => 'Company Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
        ];

        if (! $airtableId) {
            return;
        }

        $airtableEntryFields = $this->getEntry($airtableId);

        if (isset($airtableEntryFields['Profile Picture'])) {
            $image = $airtableEntryFields['Profile Picture'];
            if (isset($image) && isset($image[0]) && isset($image[0]['url'])) {
                $imageName = $this->extractProfilePicture($image[0]['url'], $person->id);
                $person->photo = $imageName ?? $this->defaultImageName;
            }
        }

        foreach ($fields as $property => $fieldName) {
            $person->$property = ! empty($airtableEntryFields[$fieldName]) ? str_replace("\n\n", "\n", $airtableEntryFields[$fieldName]) : '-';
        }

        return $person->saveQuietly();
    }

    private function extractProfilePicture(string $imageLink, int $personId)
    {
        $imageContent = @file_get_contents($imageLink);

        $person = Person::where('id', $personId)->first();

        $tempPath = sys_get_temp_dir().'/'.Str::random(10).'.jpg';
        file_put_contents($tempPath, $imageContent);
        $file = new UploadedFile(
            $tempPath,
            'profile.jpg',  // original name
            'image/jpeg',   // mime type, adjust if needed
            null,
            true           // mark as test to avoid is_uploaded_file() check
        );
        $uploader = new CustomUploader;

        $result = $uploader->uploadPersonPhotos($file);

        $person->photo_v2 = $result['large_photo'];
        $person->photo_small = $result['small_photo'];
        $person->photo = null;
        $person->saveQuietly();
        \Log::info('DONE');
        unlink($tempPath);

        return $this->imageService->createImg($imageContent, 400, 600, 'images/people/');
    }
}
