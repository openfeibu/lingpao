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
        $this->middleware('auth.api',['except' => ['extractExpressInfo']]);
    }
    public function createOrder(Request $request)
    {
        $rule = [
            'urgent' => 'sometimes|in:0,1',
            'urgent_price' => 'sometimes|numeric|min:0',
            'tip' => 'sometimes|numeric|min:0',
            'payment' => "required|integer|in:wechat,cash",


            'phone' => 'sometimes|required|regex:'.config('regex.phone'),
            'destination' => 'required',
            'description' => 'required',
            'fee' => 'required|numeric|min:2',
            'goods_fee' => 'sometimes|required|numeric|min:0',
            'pay_id' => "required|integer|in:wechat,cash",
            'pay_password' => 'sometimes|required|string',
            'platform' => 'sometimes|in:and,ios,wap,wechat',
        ];
        validateCustomParameter($request->get('order'),$rule);

        $express_rule = [
            'take_place' => 'required',
            'consignee' => 'required',
            'mobile' => 'required|regex'.config('regex.phone'),
            'address' => 'required',
            'description' => 'description',
            'take_code' => 'required',
            'express_company' => 'required',
            'express_arrive_date' => 'required',
        ];
        $expresses = $request->get('expresses');
        foreach ($expresses as $key => $express)
        {
            validateCustomParameter($express,$express_rule);
        }

    }
    public function extractExpressInfo(Request $request)
    {
        $rule = [
            'description' => 'required',
        ];
        validateParameter($rule);
        $description = $request->description;
        preg_match_all('/[0-9-_]{4,8}/',$description,$result);
        var_dump($result);exit;
    }
}