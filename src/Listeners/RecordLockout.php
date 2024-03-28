<?php

namespace Javaabu\Auth\Listeners;

use Illuminate\Auth\Events\Lockout;

class RecordLockout
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
     * @param  Lockout  $event
     * @return void
     */
    public function handle(Lockout $event)
    {
        $request = $event->request;

        // log the lockout
        activity()->withProperties($request ? $request->all() : [])
                  ->log('lockout');
    }
}
