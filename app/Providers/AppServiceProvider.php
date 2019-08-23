<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\Common\Tree;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadViewsFrom(public_path().'/themes/vender/filer', 'filer');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('tree',function(){
            return new Tree;
        });
        $this->app->bind(
            'App\Repositories\Eloquent\PageRepositoryInterface',
            \App\Repositories\Eloquent\PageRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\PageCategoryRepositoryInterface',
            \App\Repositories\Eloquent\PageCategoryRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\PageRecruitRepositoryInterface',
            \App\Repositories\Eloquent\PageRecruitRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\SettingRepositoryInterface',
            \App\Repositories\Eloquent\SettingRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\BannerRepositoryInterface',
            \App\Repositories\Eloquent\BannerRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\LinkRepositoryInterface',
            \App\Repositories\Eloquent\LinkRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\NavRepositoryInterface',
            \App\Repositories\Eloquent\NavRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\NavCategoryRepositoryInterface',
            \App\Repositories\Eloquent\NavCategoryRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\UserRepositoryInterface',
            \App\Repositories\Eloquent\UserRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\UserAddressRepositoryInterface',
            \App\Repositories\Eloquent\UserAddressRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\TaskOrderRepositoryInterface',
            \App\Repositories\Eloquent\TaskOrderRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\TakeOrderRepositoryInterface',
            \App\Repositories\Eloquent\TakeOrderRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface',
            \App\Repositories\Eloquent\TakeOrderExpressRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface',
            \App\Repositories\Eloquent\TakeOrderExtraPriceRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\UserCouponRepositoryInterface',
            \App\Repositories\Eloquent\UserCouponRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\BalanceRecordRepositoryInterface',
            \App\Repositories\Eloquent\BalanceRecordRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\TradeRecordRepositoryInterface',
            \App\Repositories\Eloquent\TradeRecordRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\RemarkRepositoryInterface',
            \App\Repositories\Eloquent\RemarkRepository::class
        );
        $this->app->bind(
            'App\Repositories\Eloquent\WithdrawRepositoryInterface',
            \App\Repositories\Eloquent\WithdrawRepository::class
        );
        $this->app->bind('filer', function ($app) {
            return new \App\Helpers\Filer\Filer();
        });
        $this->app->singleton('image', function ($app) {
            return new ImageManager($app['config']->get('image'));
        });
    }

    public function provides()
    {

    }
}
