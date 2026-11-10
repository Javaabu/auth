<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

interface CreatesNewUsers
{
    /**
     * Validate and create a user of the type
     *
     * @param  array<string, mixed>  $input
     */
    public function create(UserType $type, array $input): Model;
}
