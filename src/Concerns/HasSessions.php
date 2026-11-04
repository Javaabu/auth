<?php

namespace Javaabu\Auth\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Javaabu\Auth\Sessions\Session;

/**
 * The user's sessions, when the app uses the `multi_auth_database` session driver
 */
trait HasSessions
{
    public function sessions(): MorphMany
    {
        return $this->morphMany(Session::class, 'user');
    }

    /**
     * Log the user out of their other browsers
     */
    public function deleteOtherSessionRecords(): void
    {
        if (config('session.driver') !== 'multi_auth_database') {
            return;
        }

        $this->sessions()
            ->when(request()->hasSession(), fn ($query) => $query->where('id', '!=', request()->session()->getId()))
            ->delete();
    }
}
