<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use DB,Log;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

class PaymentNotifyController extends BaseController
{
    protected $user;

    public function __construct (TakeOrderRepositoryInterface $takeOrderRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository)
    {
        parent::__construct();
        $this->takeOrderRepository = $takeOrderRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;

    }
    public function wechatNotify()
    {
        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
        ];
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function ($notify, $fail) {
            $out_trade_no = $notify['out_trade_no'];
            $trade_no = $notify['transaction_id'];
            Log::debug("wechat_out_trade_no:".$out_trade_no);
            Log::debug("wechat_trade_no:".$trade_no);
            return $this->handleNotify($out_trade_no,$trade_no,'wechat');
           // $fail('Order not exists.');
        });
        return $response;
    }
    private function handleNotify($out_trade_no,$trade_no,$payment)
    {
        $order_type_arr = explode('-'.$out_trade_no);
        $order_type = $order_type_arr[0];
        switch ($order_type)
        {
            case 'TAKE':
                $take_order = $this->takeOrderRepository->where('order_sn',$out_trade_no)->first(['id','user_id','total_price','coupon_id']);
                $trade = array(
                    'user_id' => $this->user->id,
                    'out_trade_no' => $out_trade_no,
                    'trade_no' => $trade_no,
                    'trade_status' => 'success',
                    'type' => -1,
                    'pay_from' => 'TakeOrder',
                    'trade_type' => 'CREATE_TAKE_ORDER',
                    'price' => $take_order->total_price,
                    'payment' => $payment,
                    'description' => '发布代拿',
                );
                $this->takeOrderRepository->updateOrderStatus(['order_status' => 'new'],$take_order->id);
                $take_order->coupon_id ? $this->userCouponRepository->update(['status' => 'used'],$take_order->coupon_id) : '';
                break;
            default:
                break;
        }
        $this->tradeRecordRepository->create($trade);
        return "true";
    }
}
