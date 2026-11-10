<?php

namespace Javaabu\Auth\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Policies\UserAccountPolicy;
use Javaabu\Auth\UserType;

class DenyAllAccountPolicy extends UserAccountPolicy
{
    protected function allows(Model $user, string $action, UserType $type): bool
    {
        return false;
    }
}
