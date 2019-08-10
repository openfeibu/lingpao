<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\Auth as AuthModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TakeOrderExpress extends AuthModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.take_order.take_order_express';


}