<?php

namespace App\Helpers\Filer\Templates;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class ExtraSmall implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        $action = config('image.size.xs.action', 'fit');
        $width  = config('image.size.xs.width', 80);
        $height = config('image.size.xs.height', 60);

        if ($action == 'resize') {
            $image->resize($width, $height);
        } else {
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
        }

        if (!empty(config('image.size.xs.watermark'))) {
            $image->insert(config('image.size.xs.watermark'), 'center');
        }

        return $image;
    }
}