<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Auth\Passwords\PasswordBroker;

interface ResetPasswordContract extends HasUserTypeRedirectContract
{
    public function getBroker(): PasswordBroker;
}
