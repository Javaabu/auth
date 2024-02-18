<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

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
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $user = $event->user;

        if ($user) {
            // log the logout
            activity()
                ->causedBy($user)
                ->log('logout');
        }
    }
}
