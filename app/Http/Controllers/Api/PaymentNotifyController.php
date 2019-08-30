<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use DB,Log;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

class PaymentNotifyController extends BaseController
{
    protected $user;

    public function __construct (TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExtraPriceRepositoryInterface $takeOrderExtraPriceRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                UserAllCouponRepositoryInterface $userAllCouponRepository)
    {
        parent::__construct();
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExtraPriceRepository = $takeOrderExtraPriceRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->userAllCouponRepository = $userAllCouponRepository;

    }
    public function wechatNotify(Request $request)
    {
//        $out_trade_no = $request->out_trade_no;
//        $trade_no = $request->transaction_id;
//        return $this->handleNotify($out_trade_no,$trade_no);
//        exit;
        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
        ];
        $app = Factory::payment($config);
        $app->handlePaidNotify(function ($notify, $fail) {
            $out_trade_no = $notify['out_trade_no'];
            $trade_no = $notify['transaction_id'];
            Log::debug("wechat_out_trade_no:".$out_trade_no);
            Log::debug("wechat_trade_no:".$trade_no);
            return $this->handleNotify($out_trade_no,$trade_no);
           // $fail('Order not exists.');
        });
        return "true";
    }
    private function handleNotify($out_trade_no,$trade_no)
    {
        $order_type_arr = explode('-',$out_trade_no);
        $order_type = $order_type_arr[0];
        $trade = [];
        switch ($order_type)
        {
            case 'TAKE':
                $take_order = $this->takeOrderRepository->where('order_sn',$out_trade_no)->first(['id','user_id','total_price','payment','order_status','coupon_id']);
                if($take_order->order_status == 'unpaid')
                {
                    $trade = array(
                        'user_id' => $take_order->user_id,
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'trade_status' => 'success',
                        'type' => -1,
                        'pay_from' => 'TakeOrder',
                        'trade_type' => 'CREATE_TAKE_ORDER',
                        'price' => $take_order->total_price,
                        'payment' => $take_order->payment,
                        'description' => '发布代拿',
                    );
                    $this->takeOrderRepository->updateOrderStatus(['order_status' => 'new'],$take_order->id);
                    $take_order->coupon_id ? $this->userAllCouponRepository->usedCoupon($take_order->coupon_id,$take_order->coupon_price) : '';
                }
                break;
            case 'TAKEEXTRA':
                $extra_price = $this->takeOrderExtraPriceRepository->where('order_sn',$out_trade_no)->first();
                if($extra_price->status == 'unpaid') {
                    $take_order = $this->takeOrderRepository->where('id', $extra_price->take_order_id)->first(['id', 'user_id', 'total_price', 'payment', 'status', 'coupon_id']);
                    $trade = array(
                        'user_id' => $take_order->user_id,
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'trade_status' => 'success',
                        'type' => -1,
                        'pay_from' => 'TakeOrderExtraPrice',
                        'trade_type' => 'CREATE_TAKE_ORDER',
                        'price' => $extra_price->total_price,
                        'payment' => $extra_price->payment,
                        'description' => '代拿增加服务费',
                    );
                    $this->takeOrderExtraPriceRepository->update(['status' => 'paid'], $extra_price->id);
                }
                break;
            case 'CUSTOM':
                $custom_order = $this->customOrderRepository->where('order_sn',$out_trade_no)->first(['id','user_id','total_price','payment','order_status','coupon_id']);
                if($custom_order->order_status == 'unpaid') {
                    $trade = array(
                        'user_id' => $custom_order->user_id,
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'trade_status' => 'success',
                        'type' => -1,
                        'pay_from' => 'CustomOrder',
                        'trade_type' => 'CREATE_CUSTOM_ORDER',
                        'price' => $custom_order->total_price,
                        'payment' => $custom_order->payment,
                        'description' => '发布帮帮忙',
                    );
                    $this->customOrderRepository->updateOrderStatus(['order_status' => 'new'], $custom_order->id);
                    $custom_order->coupon_id ? $this->userAllCouponRepository->usedCoupon($custom_order->coupon_id, $custom_order->coupon_price) : '';
                }
                break;
            default:
                break;
        }
        $trade ? $this->tradeRecordRepository->create($trade) : '';
        return "true";
    }
}
