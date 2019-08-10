<?php

namespace App\Http\Controllers\Pc\Auth;

use App\Http\Controllers\Pc\Controller;
use App\Traits\RoutesAndGuards;
use App\Traits\Auth\AuthenticatesUsers;
use App\Traits\Theme\ThemeAndViews;
use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Response\Auth\Response as AuthResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use RoutesAndGuards,ThemeAndViews, ValidatesRequests, AuthenticatesUsers;

    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->response   = resolve(AuthResponse::class);
        $this->middleware('guest:user.web', ['except' => ['logout', 'verify']]);
        $this->setTheme();
    }
    /*
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect($this->redirectTo);
    }
    */

    public function showLoginForm()
    {
        $guard = $this->getGuardRoute();

        return $this->response
            ->title('登陆')
            ->layout('auth')
            ->view('auth.login')
            ->data(compact('guard'))
            ->output();
    }
    public function login(Request $request)
    {
        // 规则
        $rules = [
            'email' => 'required|string|email',
            //'password' => 'required|string|min:6|confirmed',
            'password' => 'required|string|min:6',
        ];
        $messages = [
            'email.require' => '密码不能为空',
            'email.email' => '邮箱格式不正确',
            'password.require' => '密码不能为空',
            'password.min' => '密码不能少于六个字符串',
        ];

        $this->validate($request, $rules, $messages);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    public function ajaxLogin(Request $request)
    {
        // 规则
        $rules = [
            'email' => 'required|string|email',
            //'password' => 'required|string|min:6|confirmed',
            'password' => 'required|string|min:6',
        ];
        $messages = [
            'email.require' => '密码不能为空',
            'email.email' => '邮箱格式不正确',
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

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $seconds = $this->limiter()->availableIn(
                $this->throttleKey($request)
            );

            return $this->response->message(Lang::get('auth.throttle', ['seconds' => $seconds]))
                ->status("error")
                ->code(429)
                ->url(url('/'))
                ->redirect();

        }

        if ($this->attemptLogin($request)) {
            return $this->response->message('登陆成功')
                ->status("success")
                ->code(200)
                ->url(url('/user/index'))
                ->redirect();
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->response->message(trans('auth.failed'))
            ->status("error")
            ->code(400)
            ->url(url('/'))
            ->redirect();

    }
}
