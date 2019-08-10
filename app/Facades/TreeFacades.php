<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TreeFacades extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tree';
    }
}