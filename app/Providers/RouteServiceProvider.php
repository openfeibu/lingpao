<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Request;
use App\Models\Roles;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
        if (Request::is('*/roles/role/*')) {
            Route::bind('role', function ($role) {
                $rolerepo = $this->app->make('App\Repositories\Eloquent\RoleRepositoryInterface');
                return $rolerepo->findorNew($role);
            });
        }

        if (Request::is('*/roles/permission/*')) {
            Route::bind('permission', function ($permission) {
                $permissionrepo = $this->app->make('App\Repositories\Eloquent\PermissionRepositoryInterface');
                return $permissionrepo->findorNew($permission);
            });
        }
        if (Request::is('*/page/page/*')) {
            Route::bind('page', function ($page) {
                $page = $this->app->make(\App\Repositories\Eloquent\PageRepositoryInterface::class);
                return $page->findOrNew($page);
            });
        }
        if (Request::is('*/page/category/*')) {
            Route::bind('category', function ($category) {
                $category = $this->app->make(\App\Repositories\Eloquent\PageCategoryRepositoryInterface::class);
                return $category->findOrNew($category);
            });
        }

        if (is_string(config('image.route'))) {
            $filename_pattern = '[ \w\\.\\/\\-\\@\(\)]+';
            // route to access template applied image file
            $this->app['router']->get(config('image.route').'/{template}/{filename}', [
                'uses' => 'App\Http\Controllers\ImageCacheController@getResponse',
                'as' => 'imagecache'
            ])->where(['filename' => $filename_pattern]);
        }
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
