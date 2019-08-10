<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Filer\Filer;
use App\Traits\Hashids\Hashids;
use App\Traits\Trans\Translatable;

class Banner extends BaseModel
{
    use Filer, Hashids, Slugger, Translatable, LogsActivity;

    public $timestamps = false;

    /**
     * Configuartion for the model.
     *
     * @var array
     */
    protected $config = 'model.banner.banner';

}