<?php

namespace App\Observers;

use App\Models\Person;
use App\Services\AirtableService;
use App\Services\CustomUploader;
use Illuminate\Support\Facades\Storage;

class PersonObserver
{
    protected AirtableService $airtableService;

    public function __construct(AirtableService $airtableService)
    {
        $this->airtableService = $airtableService;
    }

    public function saving(Person $person): void
    {
        if ($person->isDirty('photo')) {
            $uploader = new CustomUploader;
            $filePath = $person->photo;

            if ($filePath && Storage::disk('local')->exists($filePath)) {
                $file = new \Illuminate\Http\UploadedFile(
                    Storage::disk('local')->path($filePath),
                    basename($filePath)
                );

                $result = $uploader->uploadPersonPhotos($file);
                Storage::disk('local')->delete($filePath);
                $person->photo_v2 = $result['large_photo'];
                $person->photo_small = $result['small_photo'];
                $person->photo = null;
                $person->save();
            }
        }

        foreach (['photo_v2', 'photo_small'] as $attr) {
            if ($person->isDirty($attr) && blank($person->$attr)) {
                $original = $person->getOriginal($attr);
                if ($original) {
                    $parsed = parse_url($original);
                    $path = ltrim($parsed['path'], '/');
                    \Illuminate\Support\Facades\Storage::disk('r2')->delete($path);
                }
            }
        }
    }

    /**
     * Handle the Person "created" event.
     */
    public function created(Person $person): void
    {
        //
    }

    /**
     * Handle the Person "updated" event.
     */
    public function updated(Person $person): void
    {
        if ($person->isDirty('airtableId')) {
            $entry = $this->airtableService->loadSpeaker($person);
        }
    }

    /**
     * Handle the Person "deleted" event.
     */
    public function deleted(Person $person): void
    {
        foreach (['photo_v2', 'photo_small'] as $attr) {
            if ($person->$attr) {
                $url = $person->$attr;
                $parsed = parse_url($url);
                $path = ltrim($parsed['path'], '/');
                \Illuminate\Support\Facades\Storage::disk('r2')->delete($path);
            }
        }
    }

    /**
     * Handle the Person "restored" event.
     */
    public function restored(Person $person): void
    {
        //
    }

    /**
     * Handle the Person "force deleted" event.
     */
    public function forceDeleted(Person $person): void
    {
        //
    }
}
