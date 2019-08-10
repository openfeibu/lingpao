<?php

namespace App\Helpers\Filer\Templates;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class Small implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        $action = config('image.size.sm.action', 'fit');
        $width  = config('image.size.sm.width', 375);
        $height = config('image.size.sm.height', 235);

        if ($action == 'resize') {
            $image->resize($width, $height);
        } else {
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
        }

        if (!empty(config('image.size.sm.watermark'))) {
            $image->insert(config('image.size.sm.watermark'), 'center');
        }

        return $image;
    }
}