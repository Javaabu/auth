<?php

namespace Javaabu\Auth\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Javaabu\Auth\Listeners\RecordFailedLogin;
use Javaabu\Auth\Listeners\RecordLogin;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Failed::class => [
            RecordFailedLogin::class,
        ],

        Login::class => [
            RecordLogin::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
