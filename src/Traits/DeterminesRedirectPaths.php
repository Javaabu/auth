<?php

namespace Javaabu\Auth\Traits;

use Javaabu\Auth\Models\User;
use Javaabu\Auth\User as UserContract;

trait DeterminesRedirectPaths
{
    /**
     * Get the post register / login redirect path.
     */
    public function redirectPath(): string
    {
        return $this->determinePathForRedirectUsing()->homeUrl();
    }

    /**
     * Determine the User Model to use when determining the path for redirect.
     * Should return new
     */
    public function determinePathForRedirectUsing(): UserContract
    {
        return new User();
    }
}
