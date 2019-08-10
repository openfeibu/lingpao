<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Log;

class TakeOrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => []]);
    }
    public function createOrder()
    {

    }
}