<?php

namespace Javaabu\Auth\Traits;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;

trait InteractsWithGuards
{
    use InteractsWithUserTypes;

    public function guardName(): string
    {
        return $this->userType()->guardName();
    }

    public function getGuard(): StatefulGuard
    {
        return Auth::guard($this->guardName());
    }
}
