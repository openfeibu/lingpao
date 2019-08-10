<?php

namespace App\Http\Controllers\Pc\Auth;

use Mail;
use App\Models\User;
use App\Http\Controllers\Pc\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;
use App\Traits\RoutesAndGuards;
use App\Http\Response\ResourceResponse;

class RegisterController extends Controller
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

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/user/index';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        set_route_guard('web','user','pc');
        $this->response = app(ResourceResponse::class);
        $this->setTheme();
        $this->middleware('guest:user.web');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    /**
     *
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:10',
            'email' => 'required|string|email|max:255|unique:users',
            //'password' => 'required|string|min:6|confirmed',
            'password' => 'required|string|min:6',
        ];
        $messages = [
            'name.require' => '昵称不能为空',
            'name.max' => '昵称不能超过10个字符',
            'email.require' => '密码不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '该邮箱已被注册',
            'password.require' => '密码不能为空',
            'password.min' => '密码不能少于六个字符串',
        ];
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            return $this->response->message($validator->errors()->first())
                ->status("error")
                ->code(400)
                ->url(url('/'))
                ->redirect();
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        UserVerification::generate($user);

        UserVerification::send($user, '请验证您的邮箱');

        return $this->response->message('注册成功')
            ->status("success")
            ->code(200)
            ->url(url('/user/index'))
            ->redirect();
    }
}
