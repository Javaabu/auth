<?php

namespace Javaabu\Auth\Tests\Models\Sanctum;

use Javaabu\Auth\Tests\Factories\UserFactory;
use Javaabu\Auth\Tests\Models\User as BaseUser;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens;

    protected static function newFactory(): UserFactory
    {
        return new class extends UserFactory
        {
            protected $model = User::class;
        };
    }
}
