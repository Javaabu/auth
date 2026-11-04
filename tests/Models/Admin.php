<?php

namespace Javaabu\Auth\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\Auth\Tests\Factories\AdminFactory;
use Laravel\Sanctum\HasApiTokens;

class Admin extends BaseUser
{
    use HasApiTokens;
    use HasFactory;

    protected $table = 'admins';

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}
