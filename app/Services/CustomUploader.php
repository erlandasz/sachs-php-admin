<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class CustomUploader
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('r2');
    }

    /**
     * Resize and upload an image.
     */
    public function uploadAndResize(UploadedFile $file, string $directory, int $width, int $height): string
    {
        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME);
        $newFilename = "{$filename}-{$width}x{$height}.png";
        $path = $directory.'/'.$newFilename;
        $manager = new ImageManager(new GdDriver);
        $image = $manager->read($file->getRealPath())
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->toPng();

        $this->disk->put($path, (string) $image, 'public'); // Set visibility here

        return $this->disk->url($path); // Return the full URL

    }

    /**
     * Upload a company logo.
     *
     * @return string
     */
    public function uploadCompanyLogo(UploadedFile $file)
    {
        return $this->uploadAndResize($file, 'company-logos', 420, 210);
    }

    /**
     * Upload a person photo.
     *
     * @return string
     */
    public function uploadPersonPhotos(UploadedFile $file): array
    {
        $largePhotoPath = $this->uploadAndResize($file, 'person-photos', 400, 600);
        $smallPhotoPath = $this->uploadAndResize($file, 'person-photos', 80, 120);

        return [
            'large_photo' => $largePhotoPath,
            'small_photo' => $smallPhotoPath,
        ];
    }
}
