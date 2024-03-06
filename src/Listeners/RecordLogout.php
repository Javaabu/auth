<?php

namespace Javaabu\Auth\Listeners;

use Illuminate\Auth\Events\Logout;
use Javaabu\Auth\Models\User;

class RecordLogout
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if ($user) {
            // log the logout
            activity()
                ->causedBy($user)
                ->log('logout');
        }
    }
}
