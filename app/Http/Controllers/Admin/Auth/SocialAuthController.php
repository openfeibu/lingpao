<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use App\Traits\AdminUser\Auth\SocialAuthentication;
use App\Traits\RoutesAndGuards;
use App\Http\Response\Auth\Response as AuthResponse;

class SocialAuthController extends Controller
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
    use SocialAuthentication, RoutesAndGuards;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->response   = resolve(AuthResponse::class);
        $this->setRedirectTo();
        $this->middleware('guest', ['except' => 'logout']);
    }
}
