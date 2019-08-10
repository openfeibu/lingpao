<?php

namespace App\Traits\Auth;

use Auth;

/**
 * Trait for managing user profile.
 */
trait Common
{

    /**
     * Get the guard to be used during authentication.
     *
     * @return string|null
     */
    protected function guard()
    {
        $guard = $this->getGuard();
        return Auth::guard($guard);
    }

    /**
     * Set guard for the auth controller.
     *
     * @return response
     */
    public function setPasswordBroker()
    {
        $guard = $this->getGuard();

        if (!empty($guard)) {
            return $this->broker = current(explode(".", $guard));
        }

    }
}
