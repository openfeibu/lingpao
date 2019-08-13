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
        $data = [
            'take_code' => '',
            'express_company' => '',
        ];

        preg_match('/[\da-zA-Z]{1,}-[\da-zA-Z]{1,}-[\da-zA-Z]{1,}|[\da-zA-Z]{1,}-[\da-zA-Z]{1,}/',$description,$code_result);
        if($code_result)
        {
            $data['take_code'] = $code_result[0];
        }else{
            preg_match('/(货号|取件码|取货码|凭)(?P<code>[0-9a-zA-Z])/',$description,$code_result);
            $data['take_code'] = $code_result ? $code_result['code'] : '';
        }

        $express_companies = config('regex.express_company');

        foreach ($express_companies as $key => $express_company)
        {
            if(strpos($description,$express_company) !== false)
            {
                $data['express_company'] = $express_company;
                break;
            }
        }

        return $this->response->success()->data($data)->json();

    }
}