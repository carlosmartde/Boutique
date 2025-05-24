<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');

            // Confiar en los proxies al usar servicios como Railway
            Request::setTrustedProxies(
                [$this->app['request']->getClientIp()],
                \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL
            );
        }
    }
}
