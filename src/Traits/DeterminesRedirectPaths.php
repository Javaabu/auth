<?php

namespace Javaabu\Auth\Traits;

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
