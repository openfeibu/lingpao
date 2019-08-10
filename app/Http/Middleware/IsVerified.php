<?php

namespace App\Http\Middleware;

use Closure,Auth;
use Jrean\UserVerification\Exceptions\UserNotVerifiedException;

class IsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if(Auth::guard($guard)->check() && ! Auth::user($guard)->verified) {
            return redirect()->guest("/email-verification/index");
        }

        return $next($request);
    }
}
