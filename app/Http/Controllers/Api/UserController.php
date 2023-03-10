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
use App\Services\SessionKeyService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\WXBizDataCryptService;
use App\Services\AmapService;
use Log,Input;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserController extends BaseController
{
    public function __construct(UserRepositoryInterface $userRepository,
                                RemarkRepositoryInterface $remarkRepository,
                                DelivererIdentificationRepositoryInterface $delivererIdentificationRepository,
                                BalanceRecordRepositoryInterface $balanceRecordRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                WithdrawRepositoryInterface $withdrawRepository,
                                SessionKeyService $sessionKeyService)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => 'getRemarks']);
        $this->userRepository = $userRepository;
        $this->remarkRepository = $remarkRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->withdrawRepository = $withdrawRepository;
        $this->delivererIdentificationRepository = $delivererIdentificationRepository;
        $this->sessionKeyService = $sessionKeyService;
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

        $code = $request->input('code');
        $we_data = $this->sessionKeyService->getSessionKey($code);

        $WXBizDataCryptService = new WXBizDataCryptService($we_data['session_key']);

        $data = [];
        $errCode = $WXBizDataCryptService->decryptData($encryptedData, $iv, $data );

        if ($errCode != 0) {
             if($errCode == -41003)
             {
                 User::where('id',$user->id)->update(['token' => '']);
                 throw new UnauthorizedHttpException('jwt-auth', 'token?????????????????????');
             }
            throw new OutputServerMessageException('????????????'.$errCode);
        }

        $phone_data = json_decode($data);

        $phone = $phone_data->phoneNumber;

        User::where('id',$user->id)->update([
            'phone' => $phone,
            'session_key' => $we_data['session_key']
        ]);
        return $this->response->success('????????????')->data(['phone' => $phone])->json();
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
        return $this->response->success('????????????')->data(['city' => $data['regeocode']['addressComponent']['city']])->json();
    }

    /*??????????????????*/
    public function setPayPassword (Request $request)
    {
        $user = User::tokenAuth();
        if($user->is_pay_password){
            throw new \App\Exceptions\OutputServerMessageException('????????????????????????');
        }
        $rule = [
            'pay_password' => 'required|string',
        ];
        validateParameter($rule);

        $update = $this->userRepository->updatePayPassword($user->id,$request->pay_password);

        throw new \App\Exceptions\RequestSuccessException();
    }
    /*??????????????????*/
    public function changePayPassword (Request $request)
    {
        $user = User::tokenAuth();
        if(!$user->is_pay_password){
            throw new \App\Exceptions\OutputServerMessageException('?????????????????????');
        }
        $rule = [
            'new_pay_password' => 'required|string',
            'old_pay_password' => 'required|string',
        ];
        validateParameter($rule);
        if (!password_verify($request->old_pay_password, $user->pay_password)) {
            throw new \App\Exceptions\OutputServerMessageException('?????????????????????');
        }
        $this->userRepository->updatePayPassword($user->id,$request->new_pay_password);
        throw new \App\Exceptions\RequestSuccessException();
    }
    public function resetPayPassword(Request $request)
    {
        $user =  User::tokenAuth();
        $encryptedData = $request->input('encryptedData');
        $iv = $request->input('iv');

        $code = $request->input('code');
        $we_data = $this->sessionKeyService->getSessionKey($code);

        $WXBizDataCryptService = new WXBizDataCryptService($we_data['session_key']);

        $data = [];
        $errCode = $WXBizDataCryptService->decryptData($encryptedData, $iv, $data );

        if ($errCode != 0) {
             if($errCode == -41003)
             {
                 User::where('id',$user->id)->update(['token' => '']);
                 throw new UnauthorizedHttpException('jwt-auth', 'token?????????????????????');
             }
            throw new OutputServerMessageException('????????????'.$errCode);
        }

        $phone_data = json_decode($data);

        $phone = $phone_data->phoneNumber;

        if($phone != $user->phone)
        {
            throw new OutputServerMessageException('???????????????????????????');
        }
        $user->pay_password = '';
        $user->session_key = $we_data['session_key'];
        $user->save();

        throw new RequestSuccessException("????????????");
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
        $fp = fopen("withdraw_apply_lock.txt", "w+");
        if (flock($fp, LOCK_NB | LOCK_EX)) {
            $user = User::tokenAuth();
            if(!$user->is_pay_password){
                throw new NotFoundPayPasswordException('????????????????????????');
            }

            $rule = [
                'price' => 'required|integer|min:30',
                'pay_password' => 'required|string',
            ];

            validateParameter($rule);

            if (!password_verify($request->pay_password, $user->pay_password)) {
                throw new \App\Exceptions\OutputServerMessageException('??????????????????');
            }

            if($user->balance < $request->balance)
            {
                throw new \App\Exceptions\OutputServerMessageException('?????????????????? '.floor($user->balance).'???');
            }

            $out_trade_no = generate_order_sn();
            $price = $request->price; //????????????
            $new_balance = $user->balance - $price; //????????????

            $balanceData = array(
                'user_id' => $user->id,
                'price' => $price,
                'balance' => $new_balance,
                'out_trade_no' => $out_trade_no,
                'type' => -1,
                'trade_type' => 'WITHDRAWALS',
                'description' => '??????',
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
                'description' => '??????',
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
            throw new RequestSuccessException('?????????????????????????????????????????????????????????????????????????????????');
        }
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
                throw new OutputServerMessageException("??????????????????????????????");
            }
            if($identification->status == 'passed')
            {
                throw new OutputServerMessageException("????????????????????????????????????");
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
        throw new RequestSuccessException("??????????????????????????????");
    }
    public function uploadChatImage(Request $request)
    {
        $user = User::tokenAuth();
        $images_url = app('image_service')->uploadImages(Input::all(),'chat/'.$user->id);

        return $this->response->success()->data($images_url)->json();

    }
}
