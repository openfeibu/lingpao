<?php

namespace App\Services;

use App\Exceptions\RequestSuccessException;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use Validator,Request,DB,Log;
use App\Models\Setting;
use App\Models\User;
//use Illuminate\Http\Request;
use EasyWeChat\Factory;


class RefundService
{
    public $user;

    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                UserRepositoryInterface $userRepository,
                                BalanceRecordRepositoryInterface $balanceRecordRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                TakeOrderExtraPriceRepositoryInterface $takeOrderExtraPriceRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                UserCouponRepositoryInterface $userCouponRepository,
                                UserAllCouponRepositoryInterface $userAllCouponRepository)
    {
        $this->takeOrderRepository = $takeOrderRepository;
        $this->userRepository = $userRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->takeOrderExtraPriceRepository = $takeOrderExtraPriceRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->userCouponRepository = $userCouponRepository;
        $this->userAllCouponRepository = $userAllCouponRepository;
    }

    public function refundHandle($data,$pay_from)
    {
        $this->user = User::tokenAuth();
        switch($pay_from)
        {
            case 'TakeOrder':
                $extra_price = $this->takeOrderExtraPriceRepository->where('take_order_id',$data['id'])->where('status','paid')->first();
                if($extra_price)
                {
                    $extra_price_data = [
                        'id' => $extra_price->id,
                        'total_price' => $extra_price->total_price,
                        'order_sn' =>  $extra_price->order_sn,
                        'payment' => $extra_price->payment,
                        'trade_type' => 'CANCEL_TAKE_ORDER',
                        'description' => '取消代拿任务',
                    ];
                    return $this->takeOrderExtraPriceRefundHandle($extra_price_data);
                }
                return $this->takeOrderRefundHandle($data);
                break;
            case 'CustomOrder':
                return $this->customOrderRefundHandle($data);
            default :
                throw new \App\Exceptions\OutputServerMessageException('操作失败');
                break;
        }

    }
    public function takeOrderRefundHandle($data)
    {
        $status = false;
        switch ($data['payment']){
            case 'balance':
                $result = $this->balance($data);
                if($result['return_code'] == 'SUCCESS')
                {
                    $status = true;
                }
                break;
            case 'wechat':
                $result = $this->wechat($data);
                $status = true;
                Log::debug("代拿任务退款result:");
                Log::debug($result);
                break;
        }

        if($status)
        {
            $this->takeOrderRepository->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'refunded'],$data['id']);
            $this->refundTrade($data);
        }
    }
    public function takeOrderExtraPriceRefundHandle($data)
    {
        $status = false;

        switch ($data['payment']){
            case 'balance':
                $result = $this->balance($data);
                if($result['return_code'] == 'SUCCESS')
                {
                    $status = true;
                }
                break;
            case 'wehcat':
                $result = $this->wechat($data);
                $status = true;
                Log::debug("代拿任务服务费退款result:");
                Log::debug($result);
                break;
        }
        if($status)
        {
            $this->takeOrderExtraPriceRepository->update(['status' => 'refunded'], $data['id']);
        }
    }

    public function customOrderRefundHandle($data)
    {
        $status = false;
        switch ($data['payment'])
        {
            case 'balance':
                $result = $this->balance($data);
                if($result['return_code'] == 'SUCCESS')
                {
                    $status = true;
                }
                break;
            case 'wechat':
                $result = $this->wechat($data);

                $status = true;
                Log::debug("帮帮忙任务退款result:");
                Log::debug($result);
                break;
        }
        if($status)
        {
            $this->customOrderRepository->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'refunded'],$data['id']);
        }
    }
    private function balance($data)
    {
        $new_balance = $this->user->balance + $data['total_price'];
        $balanceData = array(
            'user_id' => $this->user->id,
            'balance' => $new_balance,
            'price'	=> $data['total_price'],
            'out_trade_no' => $data['order_sn'],
            'fee' => 0,
            'type' => 1,
            'trade_type' => $data['trade_type'],
            'description' => $data['description'],
        );
        $this->balanceRecordRepository->create($balanceData);
        $this->userRepository->update(['balance' => $new_balance],$this->user->id);

        $this->refundCommonHandle($data);
        return [
            'return_code' => 'SUCCESS',
        ];
    }
    public function wechat($data)
    {
        $config = $this->getWechatConfig();

        $app = Factory::payment($config);

        $result = $app->refund->byOutTradeNumber($data['order_sn'], $data['order_sn'], $data['total_price'] * 100, $data['total_price'] * 100, [
            'refund_desc' => $data['description'],
        ]);

        $this->refundCommonHandle($data);

        return $result;
    }
    public function refundCommonHandle($data)
    {
        isset($data['coupon_id']) && $data['coupon_id'] ? $this->userAllCouponRepository->refundCoupon($data['coupon_id'],$data['coupon_price']) : '';
        $this->refundTrade($data);
    }
    public function refundTrade($data)
    {
        $trade = array(
            'trade_status' => 'refunded',
            'type' => 1,
            'trade_type' => $data['trade_type'],
            'description' => $data['description'],
        );
        $trade_record = $this->tradeRecordRepository->where('out_trade_no',$data['order_sn'])->orderBy('id','asc')->first();
        $this->tradeRecordRepository->update($trade,$trade_record->id);
    }
    private function getWechatConfig()
    {
        return [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
            'cert_path' => config('wechat.payment.default.cert_path'),
            'key_path' => config('wechat.payment.default.key_path') ,
        ];
    }
}
