<?php

namespace App\Providers;

use Teepluss\Theme\ThemeServiceProvider as BaseThemeServiceProvider;

class ThemeServiceProvider extends BaseThemeServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Register service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton('view.finder', function ($app) {
            return new \App\Helpers\Theme\ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths'],
                null
            );
        });
        $this->app['view']->setFinder($this->app['view.finder']);

    }
    public function registerTheme()
    {
        $this->app->singleton('theme', function ($app) {
            return new \App\Theme($app['config'], $app['events'], $app['view'], $app['asset'], $app['files'], $app['breadcrumb']);
        });
        $this->app->alias('theme', 'Teepluss\Theme\Contracts\Theme');
    }
}
