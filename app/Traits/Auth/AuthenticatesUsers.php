<?php

namespace App\Traits\Auth;

use Auth;
use Crypt;
use Illuminate\Foundation\Auth\AuthenticatesUsers as IlluminateAuthenticatesUsers;
use Mail;
use Socialite;
//use Symfony\Component\HttpFoundation\Session\Session;
use Theme;
use Illuminate\Http\Request;
use Session;

trait AuthenticatesUsers
{

    use IlluminateAuthenticatesUsers, Common {
         Common::guard insteadof IlluminateAuthenticatesUsers;
    }


    public function login(Request $request)
    {
        $this->validateLogin($request);

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

    /**
     * Show email verification page.
     *
     * @param string code
     *
     * @return view
     **/
    public function verify($code = null)
    {

        if (!is_null($code)) {
            if ($this->activate($code)) {
                return redirect(guard_url('login'))->withCode(201)->withMessage('Your account is activated.');
            } else {
                return redirect(guard_url('login'))->withCode(301)->withMessage('Activation link is invalid or expired.');
            }
        }

        if (Auth::guard()->guest()) {
            return redirect(guard_url('login'));
        }

        return $this->response
            ->title('Verify email address')
            ->layout('auth')
            ->view('auth.verify')
            ->output();
    }
    /**
     * Activate the user with given activation code.
     *
     * @param string code
     *
     * @return view
     **/
    protected function activate($code)
    {
        $id = Crypt::decrypt($code);

        return User::activate($id);
    }

    protected function sendVerification()
    {
        $this->sendVerificationMail(user());
        return redirect()->back()->withCode(201)->withMessage('Verification link send to your email please check the mail for activation mail.');
    }
    /**
     * Send email verification email to the user.
     *
     * @return Response
     */
    protected function sendVerificationMail($user)
    {
        $data['confirmation_code'] = Crypt::encrypt($user->id);
        $data['guard']             = $this->getGuard();
        Mail::send('auth.emails.verify', $data, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('Verify your email address');
        });
    }
    
    public function username()
    {
        return 'email';
    }
}
