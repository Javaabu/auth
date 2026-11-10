<?php

namespace Javaabu\Auth\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\Auth\Tests\Factories\AdminFactory;
use Javaabu\Permissions\Traits\HasRoles;

class Admin extends BaseUser
{
    use HasFactory;
    use HasRoles;

    protected $table = 'admins';

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}
