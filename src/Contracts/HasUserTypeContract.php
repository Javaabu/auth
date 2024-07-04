<?php

namespace Javaabu\Auth\Contracts;

use Javaabu\Auth\User;

interface HasUserTypeContract
{
    public function userType(): User;
}
