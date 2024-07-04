<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;

interface HasGuardContract extends HasUserTypeContract
{
    public function getGuard(): StatefulGuard;

    /**
     * Name of the user guard
     */
    public function guardName(): string;
}
