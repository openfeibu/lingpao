<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Filer\Filer;
use App\Traits\Hashids\Hashids;
use App\Traits\Trans\Translatable;

class SendOrderExpressCompany extends BaseModel
{
    use Filer, Hashids, Slugger, Translatable, LogsActivity;

    protected $config = 'model.send_order.send_order_express_company';

    public static function getExpressCompanies()
    {
        return self::orderBy('order','asc')->orderBy('id','asc')->get();
    }

}