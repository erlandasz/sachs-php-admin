<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\CustomUploader;
use Illuminate\Support\Facades\Storage;

class CompanyObserver
{
    public function saving(Company $company): void
    {
        if ($company->isDirty('photo')) {
            $uploader = new CustomUploader;
            $filePath = $company->logo_name;

            if ($filePath && Storage::disk('local')->exists($filePath)) {
                $file = new \Illuminate\Http\UploadedFile(
                    Storage::disk('local')->path($filePath),
                    basename($filePath)
                );

                $result = $uploader->uploadCompanyLogo($file);
                $company->logo_name = null;
                $company->cloudinary_url = $result;
                $company->save();
            }
        }
    }

    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "restored" event.
     */
    public function restored(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        //
    }
}
