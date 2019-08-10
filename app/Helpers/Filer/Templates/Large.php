<?php

namespace App\Helpers\Filer\Templates;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class Large implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        $action = config('image.size.lg.action', 'fit');
        $width  = config('image.size.lg.width', 1000);
        $height = config('image.size.lg.height', 750);

        if ($action == 'resize') {
            $image->resize($width, $height);
        } else {
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
        }

        if (!empty(config('image.size.lg.watermark'))) {
            $image->insert(config('image.size.lg.watermark'), 'center');
        }

        return $image;
    }
}