<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class DelivererIdentification extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.user.deliverer_identification';

    protected $appends = ['student_id_card_image_full','status_desc'];

    public function getStudentIdCardImageFullAttribute()
    {
        $student_id_card_image = $this->attributes['student_id_card_image'];
        return url('/image/original/'.$student_id_card_image);
    }
    public function getStatusDescAttribute()
    {
        $status_desc = trans('deliverer_identification.status.'.$this->attributes['status']);
        return $status_desc;
    }
}