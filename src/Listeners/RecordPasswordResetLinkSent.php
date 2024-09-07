<?php

namespace Javaabu\Auth\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Javaabu\Auth\User;

class RecordPasswordResetLinkSent
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
    public function handle(PasswordResetLinkSent $event): void
    {
        /** @var User $user */
        $user = $event->user;
dd('here');
        if ($user) {
            // log the logout
            activity()
                ->causedBy($user)
                ->log('password_reset_link_sent');
        }
    }
}
