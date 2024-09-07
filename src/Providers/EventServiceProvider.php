<?php

namespace Javaabu\Auth\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Javaabu\Auth\Events\PasswordChanged;
use Javaabu\Auth\Listeners\RecordFailedLogin;
use Javaabu\Auth\Listeners\RecordLockout;
use Javaabu\Auth\Listeners\RecordLogin;
use Javaabu\Auth\Listeners\RecordLogout;
use Javaabu\Auth\Listeners\RecordPasswordChanged;
use Javaabu\Auth\Listeners\RecordPasswordReset;
use Javaabu\Auth\Listeners\RecordPasswordResetLinkSent;

class EventServiceProvider extends ServiceProvider
{
    protected function configureEmailVerification(){
        // fix for Registered Event listener getting registered multiple times
        // see https://github.com/laravel/framework/issues/50783#issuecomment-2072411615
    }

    protected $listen = [
        Failed::class => [
            RecordFailedLogin::class,
        ],

        PasswordResetLinkSent::class => [
            RecordPasswordResetLinkSent::class,
        ],

        PasswordChanged::class => [
            RecordPasswordChanged::class,
        ],

        PasswordReset::class => [
            RecordPasswordReset::class,
        ],

        Login::class => [
            RecordLogin::class,
        ],

        Logout::class => [
            RecordLogout::class,
        ],

        Lockout::class => [
            RecordLockout::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
