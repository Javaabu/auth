<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

interface UpdatesUserPasswords
{
    /**
     * Validate and change the password of a signed-in user
     *
     * @param  array<string, mixed>  $input
     */
    public function update(UserType $type, Model $user, array $input): void;
}
