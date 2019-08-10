<?php

namespace App\Services;

use Validator;
use DB;
use Log;
use App\Setting;
use App\TradeAccount;
use App\ShippingConfig;
use Illuminate\Http\Request;

class HelpService
{

	public $request;

	function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * 检验请求参数
	 */
	public function validateParameter($rules)
	{
		$validator = Validator::make($this->request->all(), $rules);
        if ($validator->fails()) {
    		throw new \App\Exceptions\OutputServerMessageException($validator->errors()->first());
        } else {
        	return true;
        }
	}
	public function validateData ($value,$custom)
	{
		if(preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$value)){
			throw new \App\Exceptions\OutputServerMessageException($custom."含非法参数");
		}
		return true;
	}


}
