<?php

namespace Javaabu\Auth;

use Illuminate\Support\ServiceProvider;
use Javaabu\Auth\Http\Middlewares\RedirectIfActivated;
use Javaabu\Auth\Http\Middlewares\RedirectIfNotActivated;
use Javaabu\Auth\Providers\EventServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('auth.php'),
            ], 'auth-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'auth');

        $this->app->register(EventServiceProvider::class);

        app('router')->aliasMiddleware('active', RedirectIfNotActivated::class);
        app('router')->aliasMiddleware('inactive', RedirectIfActivated::class);
    }
}
