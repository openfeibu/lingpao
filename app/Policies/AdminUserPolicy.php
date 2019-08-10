<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\AdminUser as AdminUserModal;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view the user.
     *
     * @param AdminUser $user
     * @param AdminUser $user
     *
     * @return bool
     */
    public function view(AdminUser $user, AdminUserModal $usermodal)
    {
       return false;
    }

    /**
     * Determine if the given user can create a user.
     *
     * @param AdminUser $user
     * @param AdminUser $user
     *
     * @return bool
     */
    public function create(AdminUser $user)
    {
        return false;
    }

    /**
     * Determine if the given user can update the given user.
     *
     * @param AdminUser $user
     * @param AdminUser $user
     *
     * @return bool
     */
    public function update(AdminUser $user, AdminUserModal $usermodal)
    {
        return false;
    }

    /**
     * Determine if the given user can delete the given user.
     *
     * @param AdminUser $user
     * @param AdminUser $user
     *
     * @return bool
     */
    public function destroy(AdminUser $user, AdminUserModal $usermodal)
    {
        return false;
    }

    /**
     * Determine if the user can perform a given action ve.
     *
     * @param [type] $user    [description]
     * @param [type] $ability [description]
     *
     * @return [type] [description]
     */
    public function before($user, $ability)
    {
        if ($user->isSuperuser()) {
            return true;
        }

    }

}
