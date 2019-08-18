<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class BalanceRecord extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.balance_record.balance_record';


}