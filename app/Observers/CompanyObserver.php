<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\CustomUploader;
use Illuminate\Support\Facades\Storage;

class CompanyObserver
{
    public function saving(Company $company): void
    {
        if ($company->isDirty('logo_name')) {
            $uploader = new CustomUploader;
            $filePath = $company->logo_name;

            if ($filePath && Storage::disk('local')->exists($filePath)) {
                $file = new \Illuminate\Http\UploadedFile(
                    Storage::disk('local')->path($filePath),
                    basename($filePath)
                );

                $result = $uploader->uploadCompanyLogo($file);
                // Delete the local file after upload
                Storage::disk('local')->delete($filePath);
                $company->logo_name = null;
                $company->cloudinary_url = $result;
                $company->save();
            }
        }

        // Delete logo from r2 if being unset
        if ($company->isDirty('cloudinary_url') && blank($company->cloudinary_url)) {
            $original = $company->getOriginal('cloudinary_url');
            if ($original) {
                $parsed = parse_url($original);
                $path = ltrim($parsed['path'], '/');
                \Illuminate\Support\Facades\Storage::disk('r2')->delete($path);
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
        if ($company->cloudinary_url) {
            $url = $company->cloudinary_url;
            $parsed = parse_url($url);
            $path = ltrim($parsed['path'], '/');
            \Illuminate\Support\Facades\Storage::disk('r2')->delete($path);
        }
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
