<?php

namespace Javaabu\Auth\Traits;

use Javaabu\Auth\User;

trait DeterminesRedirectPaths
{
    use InteractsWithGuards;

    /**
     * Get the post register / login redirect path.
     */
    public function redirectPath(): string
    {
        return $this->determinePathForRedirectUsing()->homeUrl();
    }

    public function userType(): User
    {
        return $this->determinePathForRedirectUsing();
    }
}
