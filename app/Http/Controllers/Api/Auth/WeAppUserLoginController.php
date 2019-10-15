<?php

namespace App\Http\Controllers\Api\Auth;

use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use App\Repositories\Eloquent\UserBalanceCouponRepositoryInterface;
use App\Services\SessionKeyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Log,Event;
use App\Services\WXBizDataCryptService;
use App\Repositories\Eloquent\UserRepositoryInterface;

class WeAppUserLoginController extends BaseController
{
    public function __construct(UserRepositoryInterface $userRepository,
                                UserBalanceCouponRepositoryInterface $userBalanceCouponRepository,
                                UserAllCouponRepositoryInterface $userAllCouponRepository,
                                SessionKeyService $sessionKeyService)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->userBalanceCouponRepository = $userBalanceCouponRepository;
        $this->userAllCouponRepository = $userAllCouponRepository;
        $this->sessionKeyService = $sessionKeyService;
    }
    public function code(Request $request)
    {
        $code = $request->input('code');
        $we_data = $this->sessionKeyService->getSessionKey($code);
        $token = $this->generatetoken($we_data['session_key']);
        $user_info = (object)Array();
        $user_info->openId = $we_data['openid'];
        $user_info->avatarUrl = '';
        $user_info->nickName = '';
        $user_info->city = "";
        $this->storeUser($user_info, $token, $we_data['session_key']);
        $user = $this->userRepository->getUserByToken($token);
        $user = visible_data($user->toArray(),config('model.user.user.user_visible'));

        return $this->response->success()->data($user)->json();
    }
    public function login(Request $request)
    {
        $code = $request->input('code');
        $encryptedData = $request->input('encryptedData');
        $iv = $request->input('iv');

        $data =  $data = $this->sessionKeyService->getSessionKey($code);
        $sessionKey = $data['session_key'];

        $token = $this->generatetoken($sessionKey);

        $WXBizDataCryptService = new WXBizDataCryptService($sessionKey);

        $errCode = $WXBizDataCryptService->decryptData($encryptedData, $iv, $data );

        if ($errCode != 0) {
            throw new \App\Exceptions\OutputServerMessageException($errCode);
        }

        $user_info = json_decode($data);

        $this->storeUser($user_info, $token, $sessionKey);

        $user = $this->userRepository->getUserByToken($token);

        $user = visible_data($user->toArray(),config('model.user.user.user_visible'));

        return $this->response->success()->data($user)->json();
    }

    public function generatetoken($sessionKey)
    {
        $this->token = sha1($sessionKey . mt_rand());
        return $this->token;
    }

    public function storeUser($user_info, $token, $session_key)
    {
        $open_id = $user_info->openId;
        $res = User::where('open_id', $open_id)->first();
        if (isset($res) && $res) {
            User::where('open_id', $open_id)->update([
                'avatar_url' => isset($user_info->avatarUrl) && !empty($user_info->avatarUrl) ? $user_info->avatarUrl : $res->avatar_url,
                'nickname' => isset($user_info->nickName) && !empty($user_info->nickName) ? $user_info->nickName : $res->nickname,
                'token' => $token,
                'session_key' => $session_key,
                'city' => isset($user_info->city) && !empty($user_info->city) ? $user_info->city : $res->city,
                'gender' => isset($user_info->gender) && !empty($user_info->gender) ? $user_info->gender : $res->gender,
            ]);
        } else {
            $user = User::create([
                'open_id' => $user_info->openId,
                'avatar_url' => $user_info->avatarUrl,
                'nickname' => $user_info->nickName,
                'session_key' => $session_key,
                'token' => $token,
                'gender' => isset($user_info->gender) && !empty($user_info->gender) ? $user_info->gender : 0,
            ]);
            $user_balance_coupon = $this->userBalanceCouponRepository->create([
                'user_id' => $user->id,
                'price' => setting('register_balance_coupon'),
                'balance' => setting('register_balance_coupon'),
            ]);
            $this->userAllCouponRepository->create([
                'user_id' => $user->id,
                'type' => 'balance',
                'objective_id' => $user_balance_coupon->id,
                'objective_model' => 'UserBalanceCoupon'
            ]);
        }
    }
    /*
    public function test()
    {
        for($i=7;$i<=231;$i++)
        {
            $exist_user_balance_coupon = $this->userBalanceCouponRepository->where('user_id',$i)->first();
            if(!$exist_user_balance_coupon)
            {
                $user_balance_coupon = $this->userBalanceCouponRepository->create([
                    'user_id' => $i,
                    'price' => setting('register_balance_coupon'),
                    'balance' => setting('register_balance_coupon'),
                ]);
                $this->userAllCouponRepository->create([
                    'user_id' => $i,
                    'type' => 'balance',
                    'objective_id' => $user_balance_coupon->id,
                    'objective_model' => 'UserBalanceCoupon'
                ]);
            }

        }

    }
    */
}
