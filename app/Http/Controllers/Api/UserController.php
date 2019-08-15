<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\OutputServerMessageException;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\WXBizDataCryptService;
use App\Services\AmapService;
use App\Repositories\Eloquent\UserRepositoryInterface;
use Log;

class UserController extends BaseController
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->middleware('auth.api');
        $this->userRepository = $userRepository;
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
}
