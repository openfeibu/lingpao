<?php

namespace App\Providers;

use Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class RolesServiceProvider extends ServiceProvider
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

        $this->registerBladeExtensions();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['roles.roles'];
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    private function publishResources()
    {

    }

    /**
     * Register Blade extensions.
     *
     * @return void
     */
    protected function registerBladeExtensions()
    {
        $blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

        $blade->directive('role', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->is{$expression}): ?>";
        });

        $blade->directive('endrole', function () {
            return "<?php endif; ?>";
        });

        \Blade::directive('permission', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->can{$expression}): ?>";
        });

        \Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });

        $blade->directive('level', function ($expression) {
            $level = trim($expression, '()');

            return "<?php if (Auth::check() && Auth::user()->level() >= {$level}): ?>";
        });

        $blade->directive('endlevel', function () {
            return "<?php endif; ?>";
        });

        $blade->directive('allowed', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->allowed{$expression}): ?>";
        });

        $blade->directive('endallowed', function () {
            return "<?php endif; ?>";
        });
    }
}
