<?php

namespace App\Http\Middleware\Roles;

use Closure;

class VerifyLogin
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param int|string               $permission
     *
     * @throws \App\Exceptions\User\PermissionDeniedException
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        if (user()->isNew && config('auth.verify_email')) {
            return redirect(guard_url('verify'));
        }

        if (user()->isLocked) {
            return redirect(guard_url('locked'));
        }

        return $next($request);
    }

}
