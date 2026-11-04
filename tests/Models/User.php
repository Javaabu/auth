<?php

namespace Javaabu\Auth\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\Auth\Tests\Factories\UserFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens;
    use HasFactory;

    protected $table = 'users';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
