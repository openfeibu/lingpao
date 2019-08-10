<?php

namespace App\Helpers\Filer\Templates;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class Medium implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        $action = config('image.size.md.action', 'fit');
        $width  = config('image.size.md.width', 800);
        $height = config('image.size.md.height', 600);

        if ($action == 'resize') {
            $image->resize($width, $height);
        } else {
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
        }

        if (!empty(config('image.size.md.watermark'))) {
            $image->insert(config('image.size.md.watermark'), 'center');
        }

        return $image;
    }
}