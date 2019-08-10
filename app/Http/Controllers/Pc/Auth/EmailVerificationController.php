<?php

namespace App\Http\Controllers\Pc\Auth;

use Mail,Auth;
use App\Models\User;
use App\Http\Controllers\Pc\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;
use App\Traits\RoutesAndGuards;
use App\Http\Response\ResourceResponse;

class EmailVerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers,VerifiesUsers,RoutesAndGuards;

    // 验证失败后的跳转地址
    public $redirectIfVerificationFails = '/emails/verification-result/failure';
    // 检测到用户已经验证过后的跳转地址
    public $redirectIfVerified = '/emails/verification-result/success';
    // 验证成功后的跳转地址
    public $redirectAfterVerification = '/emails/verification-result/success';

    public function __construct()
    {
        set_route_guard('web','user','pc');
        $this->response = app(ResourceResponse::class);
        $this->setTheme();
        $this->middleware('auth:user.web',['expect' => ['getVerification']]);
    }

    public function getVerificationIndex()
    {
        return $this->response->title('邮箱验证')
            ->view('user.email_verification.index', true)
            ->output();
    }
    public function required(Request $request)
    {
        $user = Auth::user();
        UserVerification::generate($user);

        UserVerification::send($user, '请验证您的邮箱');

        return $this->response->message('发送邮箱成功，请注意查收')
            ->status("success")
            ->code(200)
            ->url(url('/'))
            ->redirect();
    }
    public function getVerification($token)
    {
        if(Auth::user() && Auth::user()->verified)
        {
            return redirect('/user/index');
        }
        $user = User::where('verification_token',$token)->first();
        if($user)
        {
            $user->verified = true;
            $user->verification_token = null;
            $user->save();
            Auth::login($user);
            session()->flash('success', '恭喜您，邮箱验证成功！');
            return redirect('/user/index');
        }else{
            return $this->response->title('邮箱验证')
                ->view('user.email_verification.index', true)
                ->data([
                    'status' => 'error'
                ])
                ->output();
        }

    }
    public function getVerificationError()
    {

    }
}
