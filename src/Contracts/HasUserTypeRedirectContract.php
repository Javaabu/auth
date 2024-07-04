<?php

namespace Javaabu\Auth\Contracts;

use Javaabu\Auth\User;

interface HasUserTypeRedirectContract extends HasGuardContract
{
    public function determinePathForRedirectUsing(): User;
}
