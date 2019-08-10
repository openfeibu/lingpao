<?php

namespace App\Helpers\Filer;

use App;
use App\Traits\Filer\FileDisplay;
use App\Traits\Filer\Uploader;

class Filer
{

    use FileDisplay, Uploader;

    public function __construct()
    {
        $this->image = App::make('image');
    }

}
