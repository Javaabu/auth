<?php

namespace Javaabu\Auth\Traits;

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
}
