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
        $rule = [
            'urgent' => 'sometimes|in:0,1',
            'urgent_price' => 'sometimes|numeric|min:0',
            'tip' => 'sometimes|numeric|min:0',

            'phone' => 'sometimes|required|regex:'.config('regex.phone'),
            'destination' => 'required',
            'description' => 'required',
            'fee' => 'required|numeric|min:2',
            'goods_fee' => 'sometimes|required|numeric|min:0',
            'pay_id' => "required|integer|in:wechat,cash",
            'pay_password' => 'sometimes|required|string',
            'platform' => 'sometimes|in:and,ios,wap,wechat',
        ];
        validateParameter($rule);
    }
}