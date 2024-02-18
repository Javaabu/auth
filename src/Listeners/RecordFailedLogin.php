<?php

namespace Javaabu\Auth\Listeners;

use Illuminate\Auth\Events\Failed;
use Javaabu\Auth\Models\User;

class RecordFailedLogin
{
    public function handle(Failed $event)
    {
        $user = $event->user;

        $user = User::find($user->id);

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
