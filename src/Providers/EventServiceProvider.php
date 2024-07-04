<?php

namespace Javaabu\Auth\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Javaabu\Auth\Listeners\RecordFailedLogin;
use Javaabu\Auth\Listeners\RecordLockout;
use Javaabu\Auth\Listeners\RecordLogin;
use Javaabu\Auth\Listeners\RecordLogout;

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
