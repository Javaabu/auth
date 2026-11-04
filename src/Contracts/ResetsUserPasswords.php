<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

interface ResetsUserPasswords
{
    /**
     * Validate and set a forgotten password
     *
     * @param  array<string, mixed>  $input
     */
    public function reset(UserType $type, Model $user, array $input): void;
}
