<?php

namespace Javaabu\Auth\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Javaabu\Auth\Listeners\RecordFailedLogin;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Failed::class => [
            RecordFailedLogin::class
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }

}
