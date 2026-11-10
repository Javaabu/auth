<?php

namespace Javaabu\Auth\Tests\Models\Passport;

use Javaabu\Auth\Models\User as BaseUser;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends BaseUser implements OAuthenticatable
{
    use HasApiTokens;

    protected $table = 'users';
}
