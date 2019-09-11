<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class Withdraw extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.user.withdraw';

    protected $appends = ['status_desc'];

    public function getStatusDescAttribute()
    {
        return trans('withdraw.status.'.$this->attributes['status']);
    }
}