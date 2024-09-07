<?php

namespace Javaabu\Auth\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Javaabu\Auth\User;

class RecordPasswordReset
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
    public function handle(PasswordReset $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if ($user) {
            // log the logout
            activity()
                ->causedBy($user)
                ->log('password_reset');
        }
    }
}
