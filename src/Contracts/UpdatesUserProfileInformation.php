<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

interface UpdatesUserProfileInformation
{
    /**
     * Validate and update the user's name and email
     *
     * @param  array<string, mixed>  $input
     */
    public function update(UserType $type, Model $user, array $input): void;
}
