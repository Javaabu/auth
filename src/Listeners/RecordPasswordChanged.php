<?php

namespace Javaabu\Auth\Listeners;

use Javaabu\Auth\Events\PasswordChanged;
use Javaabu\Auth\User;

class RecordPasswordChanged
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
    public function handle(PasswordChanged $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if ($user) {
            // log the logout
            $log = activity()
                ->performedOn($user);


            if ($causer = $event->causer) {
                $log->causedBy($causer);
            }

            $log->log('password_changed');
        }
    }
}
