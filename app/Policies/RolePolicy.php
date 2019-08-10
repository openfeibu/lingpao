<?php

namespace App\Policies;

use App\Contracts\AdminUserPolicy;
use App\Models\Role;

class RolePolicy
{

    /**
     * Determine if the given user can view the role.
     *
     * @param AdminUserPolicy $user
     * @param Role $role
     *
     * @return bool
     */
    public function view(AdminUserPolicy $user, Role $role)
    {
        return false;
    }

    /**
     * Determine if the given user can create a role.
     *
     * @param AdminUserPolicy $user
     * @param Role $role
     *
     * @return bool
     */
    public function create(AdminUserPolicy $user)
    {
        return  false;
    }

    /**
     * Determine if the given user can update the given role.
     *
     * @param AdminUserPolicy $user
     * @param Role $role
     *
     * @return bool
     */
    public function update(AdminUserPolicy $user, Role $role)
    {

        return false;
    }

    /**
     * Determine if the given user can delete the given role.
     *
     * @param AdminUserPolicy $user
     * @param Role $role
     *
     * @return bool
     */
    public function destroy(AdminUserPolicy $user, Role $role)
    {
        return false;
    }

    /**
     * Determine if the given user can verify the given role.
     *
     * @param AdminUserPolicy $user
     * @param Role $role
     *
     * @return bool
     */
    public function verify(AdminUserPolicy $user, Role $role)
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
