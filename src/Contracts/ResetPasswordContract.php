<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;

interface ResetPasswordContract
{
    public function getBroker(): PasswordBroker;

    public function getGuard(): StatefulGuard;

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User;
}
