<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class Remark extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.remark.remark';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    public function deliverer()
    {
        return $this->belongsTo('App\Models\User','deliverer_id');
    }
}