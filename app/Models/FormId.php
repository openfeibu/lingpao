<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Filer\Filer;
use App\Traits\Hashids\Hashids;
use App\Traits\Trans\Translatable;

class FormId extends BaseModel
{
    use Filer, Hashids, Slugger, Translatable, LogsActivity;

    protected $config = 'model.form_id.form_id';

    public static function getFormId($user_id)
    {
        return self::where('status','unused')->where('created_at','>=',date('Y-m-d H:i:s',strtotime("-7 day")))->where('user_id',$user_id)->orderBy('id','asc')->value('form_id');
    }
}