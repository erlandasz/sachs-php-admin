<?php

namespace App\Services;

use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class ImageService
{
    protected string $canvasColor = 'ffffff';

    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver);
    }

    /**
     * Creates a company image from image content.
     *
     * @param  string  $imageContent  Binary image data
     * @param  string  $path  Directory to save to (must end with '/')
     */
    public function createCompanyImg(string $imageContent, int $width, int $height, string $path): ?string
    {
        $filename = Str::uuid()->toString().'.png';

        try {
            $image = $this->manager->read($imageContent);
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });

            $image->resizeCanvas($width, $height, 'center', false, $this->canvasColor);
            $image->save($path.$filename);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Creates an image from image content with aspect ratio handling.
     *
     * @param  string  $imageContent  Binary image data
     * @param  string  $path  Directory to save to (must end with '/')
     */
    public function createImg(string $imageContent, int $width, int $height, string $path): ?string
    {
        $filename = Str::uuid()->toString().'.png';

        try {
            $image = $this->manager->read($imageContent);
            $imageWidth = $image->width();
            $imageHeight = $image->height();
            $ratio = $imageWidth / $imageHeight;
            $desiredRatio = $width / $height;

            if ($ratio < $desiredRatio) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $image->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $image->resizeCanvas($width, $height, 'center', false, $this->canvasColor);
            $image->save($path.$filename);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
