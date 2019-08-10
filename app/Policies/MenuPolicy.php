<?php

namespace App\Policies;

use App\Contracts\AdminUserPolicy;
use App\Models\Menu;

class MenuPolicy
{

    /**
     * Determine if the given user can view the menu.
     *
     * @param AdminUserPolicy $user
     * @param Menu $menu
     *
     * @return bool
     */
    public function view(AdminUserPolicy $user, Menu $menu)
    {
        return false;
    }

    /**
     * Determine if the given user can create a menu.
     *
     * @param AdminUserPolicy $user
     * @param Menu $menu
     *
     * @return bool
     */
    public function create(AdminUserPolicy $user)
    {
        return false;
    }

    /**
     * Determine if the given user can update the given menu.
     *
     * @param AdminUserPolicy $user
     * @param Menu $menu
     *
     * @return bool
     */
    public function update(AdminUserPolicy $user, Menu $menu)
    {
        return false;

    }

    /**
     * Determine if the given user can delete the given menu.
     *
     * @param AdminUserPolicy $user
     * @param Menu $menu
     *
     * @return bool
     */
    public function destroy(AdminUserPolicy $user, Menu $menu)
    {

        return false;
    }

    /**
     * Determine if the given user can verify the given menu.
     *
     * @param AdminUserPolicy $user
     * @param Menu $menu
     *
     * @return bool
     */
    public function verify(AdminUserPolicy $user, Menu $menu)
    {
        return false;
    }

    /**
     * Determine if the given user can approve the given menu.
     *
     * @param AdminUserPolicy $user
     * @param Menu $menu
     *
     * @return bool
     */
    public function approve(AdminUserPolicy $user, Menu $menu)
    {
        return false;
    }

    /**
     * Determine if the user can perform a given action .
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
