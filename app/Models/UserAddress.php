<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\Auth as AuthModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserAddress extends AuthModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.user.user_address';


}