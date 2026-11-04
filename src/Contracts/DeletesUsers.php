<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

interface DeletesUsers
{
    /**
     * Delete the user's own account
     */
    public function delete(UserType $type, Model $user): void;
}
