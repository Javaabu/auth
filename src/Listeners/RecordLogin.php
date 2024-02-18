<?php

namespace Javaabu\Auth\Listeners;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;

class RecordLogin
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
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;

        if ($user) {
            // reset login attempts if not already locked out
            if (! $user->is_locked_out) {
                $user->resetLoginAttempts();
            }

            // save the last_login_at
            $user->last_login_at = Carbon::now();
            $user->save();

            // log the login
            activity()
                ->causedBy($user)
                ->log('login');
        }
    }
}
