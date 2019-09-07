<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Repositories\Eloquent\DelivererIdentificationRepositoryInterface;
use App\Repositories\Eloquent\WithdrawRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\RemarkRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\WXBizDataCryptService;
use App\Services\AmapService;
use Log,Input;

class UserController extends BaseController
{
    public function __construct(UserRepositoryInterface $userRepository,
                                RemarkRepositoryInterface $remarkRepository,
                                DelivererIdentificationRepositoryInterface $delivererIdentificationRepository,
                                BalanceRecordRepositoryInterface $balanceRecordRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                WithdrawRepositoryInterface $withdrawRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => 'getRemarks']);
        $this->userRepository = $userRepository;
        $this->remarkRepository = $remarkRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->withdrawRepository = $withdrawRepository;
        $this->delivererIdentificationRepository = $delivererIdentificationRepository;
    }
    public function getUser(Request $request)
    {
        $user = User::tokenAuth();
        $user = visible_data($user->toArray(),config('model.user.user.user_visible'));

        return $this->response->success()->data($user)->json();
    }
    public function getOther(Request $request)
    {
        $rule = [
            'user_id' => 'required',
        ];
        validateParameter($rule);
        $user_id = $request->user_id;
        $other = $this->userRepository->getOther($user_id);

        $other =  visible_data($other->toArray(),config('model.user.user.other_visible'));
        return $this->response->success()->data($other)->json();

    }
    public function getRemarks(Request $request)
    {
        $rule = [
            'deliverer_id' => 'required',
        ];
        validateParameter($rule);
        $limit = $request->input('limit',config('app.limit'));
        $remarks = $this->remarkRepository
            ->where('deliverer_id',$request->deliverer_id)
            ->orderBy('id','desc')
            ->paginate($limit);

        foreach ($remarks as $key => $remark)
        {
            $remark->user = $remark->user;
            $remark->deliverer = $remark->deliverer;
        }
        $remarks_data = $remarks->toArray()['data'];

        foreach ($remarks_data as $key => $remark)
        {
            $remarks_data[$key]['user'] =  visible_data($remark['user'],config('model.user.user.other_visible'));
            $remarks_data[$key]['deliverer'] =  visible_data($remark['deliverer'],config('model.user.user.other_visible'));
        }

        return $this->response->success()->count($remarks->total())->data($remarks_data)->json();
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
    public function resetPayPassword(Request $request)
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

        if($phone != $user->phone)
        {
            throw new OutputServerMessageException('手机号码验证不一致');
        }
        $user->pay_password = '';
        $user->save();

        throw new RequestSuccessException("验证成功");
    }
    public function getBalance(Request $request)
    {
        $user = User::tokenAuth();
        return $this->response->success()->data(['balance' => $user->balance])->json();
    }
    public function getMainInfo()
    {
        $user = User::tokenAuth();
        return $this->response->success()->data(['balance' => $user->balance,'role' => $user->role])->json();
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
            'account' => 'wechat',
            'status' => 'checking',
        );
        $this->withdrawRepository->create($withdrawData);
        $this->userRepository->update(['balance' => $new_balance],$user->id);
        $this->balanceRecordRepository->create($balanceData);
        $this->tradeRecordRepository->create($trade);
        throw new RequestSuccessException('您的提现申请已提交，我们会尽快给您转账，请您耐心等待！');
    }
    public function uploadStudentImage(Request $request)
    {
        $user = User::tokenAuth();
        $images_url = app('image_service')->uploadImages(Input::all(),'deliverer');

        return $this->response->success()->data($images_url)->json();

    }
    public function getDelivererIdentification(Request $request)
    {
        $user = User::tokenAuth();

        $identification = $this->delivererIdentificationRepository->where('user_id',$user->id)->first();
        $identification_data = $identification ? $identification->toArray() : [];
        $identification_data ? $identification_data['student_id_card_image_full'] = $identification->student_id_card_image_full : '';
        return $this->response->success()->data($identification_data)->json();
    }
    public function beDeliverer(Request $request)
    {
        $user = User::tokenAuth();
        $rule = [
            'name' => 'required|string',
            'student_id_card_image' => 'required|string',
        ];

        validateParameter($rule);

        $identification = $this->delivererIdentificationRepository->where('user_id',$user->id)->first();
        if($identification)
        {
            if($identification->status == 'checking')
            {
                throw new OutputServerMessageException("审核中，请勿重复提交");
            }
            if($identification->status == 'passed')
            {
                throw new OutputServerMessageException("已通过审核，请勿重复提交");
            }
            $this->delivererIdentificationRepository->update([
                'name' => $request->name,
                'student_id_card_image' => $request->student_id_card_image,
                'status' => 'checking',
            ],$identification->id);
            throw new RequestSuccessException();
        }

        $this->delivererIdentificationRepository->create([
            'user_id' => $user->id,
            'name' => $request->name,
            'student_id_card_image' => $request->student_id_card_image,
            'status' => 'checking',
        ]);
        throw new RequestSuccessException("提交成功，请等待审核");
    }
    public function uploadChatImage(Request $request)
    {
        $user = User::tokenAuth();
        $images_url = app('image_service')->uploadImages(Input::all(),'chat/'.$user->id);

        return $this->response->success()->data($images_url)->json();

    }
}
