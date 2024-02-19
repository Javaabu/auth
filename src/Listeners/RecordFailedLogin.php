<?php

namespace Javaabu\Auth\Listeners;

use Illuminate\Auth\Events\Failed;
use Javaabu\Auth\Models\User;

class RecordFailedLogin
{
    public function handle(Failed $event)
    {
        /** @var User $user */
        $user = $event->user;

        if ($user) {
            // increment the attempts
            $user->incrementLoginAttempts();
            $user->save();

            // log the failed login
            activity()
                ->causedBy($user)
                ->log('failed_login');
        }
    }
}
