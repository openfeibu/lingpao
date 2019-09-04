<?php

namespace App\Http\Controllers\Api;

use App\Events\GatewayWorker\Events;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Setting;
use EasyWeChat\Factory;
use Log;

class WechatController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
   public function index()
   {
       $config = [
           'app_id' => config('wechat.mini_program.default.app_id'),
           'mch_id' => config('wechat.payment.default.mch_id'),
           'key' => config('wechat.payment.default.key'),
           'token' => config('wechat.payment.default.token'),
           'aes_key' => config('wechat.payment.default.key'),

       ];

       $app = Factory::officialAccount($config);
       $response = $app->server->serve();
       return $response;
       //$response->send(); // Laravel 里请使用：return $response;
   }
}
