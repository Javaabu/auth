<?php

namespace Javaabu\Auth\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\Auth\Tests\Factories\UserFactory;
use Javaabu\Permissions\Traits\HasRoles;

class User extends BaseUser
{
    use HasFactory;
    use HasRoles;

    protected $table = 'users';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
