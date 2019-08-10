<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\AdminUser'  => \App\Policies\AdminUserPolicy::class,
        'App\Models\Role' => \App\Policies\RolePolicy::class,
// Bind Permission policy
        'App\Models\Permission' => \App\Policies\PermissionPolicy::class,
        'App\Models\Menu' => \App\Policies\MenuPolicy::class,
        \App\Models\Page::class => \App\Policies\PagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //
    }
}
