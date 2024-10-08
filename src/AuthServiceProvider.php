<?php

namespace Javaabu\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Javaabu\Auth\Http\Middlewares\RedirectIfActivated;
use Javaabu\Auth\Http\Middlewares\RedirectIfActivatedAndEmailVerified;
use Javaabu\Auth\Http\Middlewares\RedirectIfEmailNotVerified;
use Javaabu\Auth\Http\Middlewares\RedirectIfEmailVerificationNotNeeded;
use Javaabu\Auth\Http\Middlewares\RedirectIfNotActivated;
use Javaabu\Auth\PasswordUpdate\Middleware\RedirectIfPasswordUpdateNotRequired;
use Javaabu\Auth\PasswordUpdate\Middleware\RedirectIfPasswordUpdateRequired;
use Javaabu\Auth\Providers\EventServiceProvider;
use Javaabu\Auth\Session\MultiAuthDatabaseSessionHandler;

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
                __DIR__.'/../config/config.php' => config_path('auth.php'),
            ], 'auth-config');

            $this->publishes([
                __DIR__ . '/../lang' => lang_path('vendor/auth'),
            ], 'auth-translations');
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'auth');

        Session::extend('multi_auth_database', function (Application $app) {
            $table = config('session.table');

            $lifetime = config('session.lifetime');

            $connection = config('session.connection');

            $db_connection = $app->make('db')->connection($connection);

            return new MultiAuthDatabaseSessionHandler($db_connection, $table, $lifetime, $app);
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'auth');

        $this->app->register(EventServiceProvider::class);

        $this->registerMiddlewareAliases();
    }

    public function registerMiddlewareAliases(): void
    {
        app('router')->aliasMiddleware('active', RedirectIfNotActivated::class);
        app('router')->aliasMiddleware('inactive', RedirectIfActivated::class);
        app('router')->aliasMiddleware('password-update-not-required', RedirectIfPasswordUpdateRequired::class);
        app('router')->aliasMiddleware('password-update-required', RedirectIfPasswordUpdateNotRequired::class);
        app('router')->aliasMiddleware('needs-verification', RedirectIfEmailVerificationNotNeeded::class);
        app('router')->aliasMiddleware('email.verified', RedirectIfEmailNotVerified::class);
        app('router')->aliasMiddleware('inactive-email.unverified', RedirectIfActivatedAndEmailVerified::class);
    }
}
