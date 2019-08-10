<?php

namespace App\Policies;

use App\Models\AdminUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Page;

class PagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view the page.
     *
     * @param AdminUser $user
     * @param Page $page
     *
     * @return bool
     */
    public function view(AdminUser $user, Page $page)
    {

        if ($user->canDo('page.page.view')) {
            return true;
        }

        return $user->id == $page->user_id;
    }

    /**
     * Determine if the given user can create a page.
     *
     * @param AdminUser $user
     *
     * @return bool
     */
    public function create(AdminUser $user)
    {
        return $user->canDo('page.page.create');
    }

    /**
     * Determine if the given user can update the given page.
     *
     * @param AdminUser $user
     * @param Page $page
     *
     * @return bool
     */
    public function update(AdminUser $user, Page $page)
    {

        if ($user->canDo('page.page.update')) {
            return true;
        }

        return $user->id == $page->user_id;
    }

    /**
     * Determine if the given user can delete the given page.
     *
     * @param AdminUser $user
     * @param Page $page
     *
     * @return bool
     */
    public function destroy(AdminUser $user, Page $page)
    {

        if ($user->canDo('page.page.delete')) {
            return true;
        }

        return $user->id == $page->user_id;
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
