<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;


class ImageProcessor
{
    public function process($image, $imageName, $variants = [])
    {
        foreach ($variants as $variant) {
            $path = $variant['path'];
            File::makeDirectory($path, 0755, true, true);

            $img = Image::read($image->getRealPath());

            if (isset($variant['cover'])) {
                $img->cover($variant['cover'][0], $variant['cover'][1], $variant['cover'][2] ?? 'center');
            }
            if (isset($variant['resize'])) {
                $img->resize($variant['resize'][0], $variant['resize'][1], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // $img->save($path . '/' . $imageName);
            $img->save($path . '/' . $imageName, 90);
        }
        Log::info('Image processed: ' . $imageName);
    }


    /**
     * Clean up old image files from specified paths
     *
     * @param string $filename The filename to delete (without path)
     * @param array $paths Array of directory paths to clean from
     * @return void
     */
    public function cleanup($filename, array $paths)
    {
        foreach ($paths as $path) {
            $fullPath = $path . '/' . $filename;

            if (File::exists($fullPath)) {
                try {
                    File::delete($fullPath);
                    Log::info("Deleted old file: {$fullPath}");
                } catch (\Exception $e) {
                    Log::warning("Failed to delete file: {$fullPath}. Error: " . $e->getMessage());
                }
            }
        }
    }
}
