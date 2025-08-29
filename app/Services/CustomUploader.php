<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class CustomUploader
{
    protected $disk;

    protected $publicBaseUrl;

    public function __construct()
    {
        $this->disk = Storage::disk('r2');
        // Set your public R2 base URL here:
        $this->publicBaseUrl = 'https://pub-b4473a0493b84baea768f8d46eb872a7.r2.dev/';
    }

    /**
     * Resize and upload an image.
     * Returns the public URL.
     */
    public function uploadAndResize(UploadedFile $file, string $directory, int $width, int $height): string
    {
        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME);
        $newFilename = "{$filename}-{$width}x{$height}.png";
        $path = $directory.'/'.$newFilename;
        $manager = new ImageManager(new Driver);

        // Read and resize the image, keeping aspect ratio
        $image = $manager->read($file->getRealPath())
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

        // Encode to PNG
        $finalImage = $image->toPng();

        // Save or upload the image
        $this->disk->put($path, (string) $finalImage, ['visibility' => 'public']);

        // Return the full public URL
        return $this->publicBaseUrl.ltrim($path, '/');
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
     */
    public function uploadPersonPhotos(UploadedFile $file): array
    {
        $largePhotoUrl = $this->uploadAndResize($file, 'person-photos', 400, 600);
        $smallPhotoUrl = $this->uploadAndResize($file, 'person-photos', 80, 120);

        \Log::info($largePhotoUrl);
        \Log::info($smallPhotoUrl);

        return [
            'large_photo' => $largePhotoUrl,
            'small_photo' => $smallPhotoUrl,
        ];
    }
}
