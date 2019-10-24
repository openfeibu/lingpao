<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use App\Repositories\Eloquent\SendOrderRepositoryInterface;
use App\Repositories\Eloquent\SendOrderCarriageRepositoryInterface;
use App\Services\MessageService;
use DB,Log;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

class PaymentNotifyController extends BaseController
{
    protected $user;

    public function __construct (TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExtraPriceRepositoryInterface $takeOrderExtraPriceRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                SendOrderRepositoryInterface $sendOrderRepository,
                                SendOrderCarriageRepositoryInterface $sendOrderCarriageRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                UserAllCouponRepositoryInterface $userAllCouponRepository)
    {
        parent::__construct();
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExtraPriceRepository = $takeOrderExtraPriceRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->sendOrderRepository = $sendOrderRepository;
        $this->sendOrderCarriageRepository = $sendOrderCarriageRepository;
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
                    $take_order = $this->takeOrderRepository->where('id', $extra_price->take_order_id)->first(['id', 'user_id','deliverer_id', 'total_price', 'payment', 'order_status', 'coupon_id','deliverer_price']);
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
                    $this->takeOrderRepository->update(['deliverer_price' => $take_order->deliverer_price+$extra_price->total_price],$take_order->id);
                    //通知 接单人
                    $message_data = [
                        'user_id' => $take_order->deliverer_id,
                        'order_sn' => $out_trade_no,
                        'task_type'=> 'take_order',
                        'type' => 'extra_price_paid',
                        'total_price' => $extra_price->total_price
                    ];
                    app(MessageService::class)->sendMessage($message_data);
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
            case 'SEND':
                $send_order = $this->sendOrderRepository->where('order_sn',$out_trade_no)->first(['id','user_id','total_price','payment','order_status','coupon_id']);
                if($send_order->order_status == 'unpaid')
                {
                    $trade = array(
                        'user_id' => $send_order->user_id,
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'trade_status' => 'success',
                        'type' => -1,
                        'pay_from' => 'SendOrder',
                        'trade_type' => 'CREATE_SEND_ORDER',
                        'price' => $send_order->total_price,
                        'payment' => $send_order->payment,
                        'description' => '发布代寄',
                    );
                    $this->sendOrderRepository->updateOrderStatus(['order_status' => 'new'],$send_order->id);
                    $send_order->coupon_id ? $this->userAllCouponRepository->usedCoupon($send_order->coupon_id,$send_order->coupon_price) : '';
                }
                break;
            case 'SENDCARRIAGE':
                $carriage = $this->sendOrderCarriageRepository->where('order_sn',$out_trade_no)->first();
                if($carriage->status == 'unpaid') {
                    $send_order = $this->sendOrderRepository->where('id', $carriage->send_order_id)->first(['id', 'user_id','deliverer_id', 'total_price', 'payment', 'order_status', 'coupon_id','deliverer_price']);
                    $trade = array(
                        'user_id' => $send_order->user_id,
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'trade_status' => 'success',
                        'type' => -1,
                        'pay_from' => 'SendOrderCarriage',
                        'trade_type' => 'CREATE_SEND_ORDER',
                        'price' => $carriage->total_price,
                        'payment' => $carriage->payment,
                        'description' => '代寄运费',
                    );
                    $this->sendOrderCarriageRepository->update(['status' => 'paid'], $carriage->id);
                    $this->sendOrderRepository->update(['deliverer_price' => $send_order->deliverer_price+$carriage->total_price],$send_order->id);
                    //通知 接单人
                    $message_data = [
                        'user_id' => $send_order->deliverer_id,
                        'order_sn' => $out_trade_no,
                        'task_type'=> 'send_order',
                        'type' => 'carriage_paid',
                        'total_price' => $carriage->total_price
                    ];
                    app(MessageService::class)->sendMessage($message_data);
                }
                break;
            default:
                break;
        }
        $trade ? $this->tradeRecordRepository->create($trade) : '';
        return "true";
    }
}
