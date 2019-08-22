<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Repositories\Eloquent\WithdrawRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\WXBizDataCryptService;
use App\Services\AmapService;
use Log;

class UserController extends BaseController
{
    public function __construct(UserRepositoryInterface $userRepository,
                                BalanceRecordRepositoryInterface $balanceRecordRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                WithdrawRepositoryInterface $withdrawRepository)
    {
        parent::__construct();
        $this->middleware('auth.api');
        $this->userRepository = $userRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->withdrawRepository = $withdrawRepository;
    }
    public function getUser(Request $request)
    {
        $user = User::tokenAuth();
        $user = visible_data($user->toArray(),config('model.user.user.user_visible'));
        return response()->json([
            'code' => '200',
            'data' => $user,
        ]);
    }
    public function submitPhone(Request $request)
    {
        $user =  User::tokenAuth();
        $encryptedData = $request->input('encryptedData');
        $iv = $request->input('iv');

        $WXBizDataCryptService = new WXBizDataCryptService($user['session_key']);

        $data = [];
        $errCode = $WXBizDataCryptService->decryptData($encryptedData, $iv, $data );

        if ($errCode != 0) {
            throw new OutputServerMessageException('错误码：'.$errCode);
        }

        $phone_data = json_decode($data);

        $phone = $phone_data->phoneNumber;

        User::where('id',$user->id)->update([
            'phone' => $phone
        ]);
        return $this->response->success('提交成功')->data(['phone' => $phone])->json();
    }
    public function submitLocation(Request $request)
    {
        $user =  User::tokenAuth();
        $longitude = $request->input('longitude','');
        $latitude =  $request->input('latitude','');
        $amap_service = new AmapService();

        $data = $amap_service->geocode_regeo($longitude.','.$latitude);

        User::where('id',$user->id)->update([
            'longitude' => $longitude,
            'latitude' => $latitude,
            'city' => $data['regeocode']['addressComponent']['city'],
        ]);
        return $this->response->success('提交成功')->data(['city' => $data['regeocode']['addressComponent']['city']])->json();
    }

    /*设置支付密码*/
    public function setPayPassword (Request $request)
    {
        $user = User::tokenAuth();
        if($user->is_pay_password){
            throw new \App\Exceptions\OutputServerMessageException('已设置过支付密码');
        }
        $rule = [
            'pay_password' => 'required|string',
        ];
        validateParameter($rule);

        $update = $this->userRepository->updatePayPassword($user->id,$request->pay_password);

        throw new \App\Exceptions\RequestSuccessException();
    }
    /*修改支付密码*/
    public function changePayPassword (Request $request)
    {
        $user = User::tokenAuth();
        if(!$user->is_pay_password){
            throw new \App\Exceptions\OutputServerMessageException('未设置支付密码');
        }
        $rule = [
            'new_pay_password' => 'required|string',
            'old_pay_password' => 'required|string',
        ];
        validateParameter($rule);
        if (!password_verify($request->old_pay_password, $user->pay_password)) {
            throw new \App\Exceptions\OutputServerMessageException('原支付密码错误');
        }
        $this->userRepository->updatePayPassword($user->id,$request->new_pay_password);
        throw new \App\Exceptions\RequestSuccessException();
    }
    public function getBalance(Request $request)
    {
        $user = User::tokenAuth();
        return $this->response->success()->data(['balance' => $user->balance])->json();
    }

    public function withdrawApply (Request $request)
    {
        $user = User::tokenAuth();
        if(!$user->is_pay_password){
            throw new NotFoundPayPasswordException('请先设置支付密码');
        }

        $rule = [
            'price' => 'required|integer|min:30',
            'pay_password' => 'required|string',
        ];

        validateParameter($rule);

        if (!password_verify($request->pay_password, $user->pay_password)) {
            throw new \App\Exceptions\OutputServerMessageException('支付密码错误');
        }

        if($user->balance < $request->balance)
        {
            throw new \App\Exceptions\OutputServerMessageException('最多只能提取 '.floor($user->balance).'元');
        }

        $out_trade_no = generate_order_sn();
        $price = $request->price; //出账金额
        $new_balance = $user->balance - $price; //钱包余额

        $balanceData = array(
            'user_id' => $user->id,
            'price' => $price,
            'balance' => $new_balance,
            'out_trade_no' => $out_trade_no,
            'type' => -1,
            'trade_type' => 'WITHDRAWALS',
            'description' => '提现',
        );
        $trade_no = 'BALANCE-'.$out_trade_no;
        $trade = array(
            'user_id' => $user->id,
            'out_trade_no' => $out_trade_no,
            'trade_no' => $trade_no,
            'trade_status' => 'checking',
            'type' => -1,
            'pay_from' => 'WITHDRAWAL',
            'trade_type' => 'WITHDRAWALS',
            'price' => $price,
            'payment' => 'balance',
            'description' => '提现',
        );
        $withdrawData = array(
            'user_id' => $user->id,
            'partner_trade_no' => $out_trade_no,
            'price' => $price,
            'status' => 'checking',
        );
        $this->withdrawRepository->create($withdrawData);
        $this->userRepository->update(['balance' => $new_balance],$user->id);
        $this->balanceRecordRepository->create($balanceData);
        $this->tradeRecordRepository->create($trade);
        throw new RequestSuccessException('您的提现申请已提交，我们会尽快给您转账，请您耐心等待！');
    }
}
