<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AdminUserServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishResources();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind facade
        $this->app->bind('user', function ($app) {
            return $this->app->make('App\AdminUser');
        });
        $this->app->bind('roles.roles', function ($app) {
            return $this->app->make('App\Roles');
        });
        // Bind User to repository
        $this->app->bind(
            'App\Repositories\Eloquent\AdminUserRepositoryInterface',
            \App\Repositories\Eloquent\AdminUserRepository::class
        );
        // Bind Role to repository
        $this->app->bind(
            'App\Repositories\Eloquent\RoleRepositoryInterface',
            \App\Repositories\Eloquent\RoleRepository::class
        );
        // Bind Permission to repository
        $this->app->bind(
            'App\Repositories\Eloquent\PermissionRepositoryInterface',
            \App\Repositories\Eloquent\PermissionRepository::class
        );
        $this->app->register(\Laravel\Socialite\SocialiteServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['user','roles.roles'];
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    private function publishResources()
    {

    }
}
