<?php

namespace App\Providers;

use Dingo\Api\Provider\LaravelServiceProvider as DingoServiceProviders;
use App\Exceptions\ApiHandler;

class DingoServiceProvider extends DingoServiceProviders
{

    protected function registerExceptionHandler()
    {
        $this->app->singleton('api.exception', function ($app) {
            return new ApiHandler($app['Illuminate\Contracts\Debug\ExceptionHandler'], $this->config('errorFormat'), $this->config('debug'));
        });
    }
}

