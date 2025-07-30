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

                  $img->save($path . '/' . $imageName);
            }
            Log::info('Image processed: ' . $imageName);
      }
}
