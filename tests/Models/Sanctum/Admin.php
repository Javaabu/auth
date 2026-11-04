<?php

namespace Javaabu\Auth\Tests\Models\Sanctum;

use Javaabu\Auth\Tests\Factories\AdminFactory;
use Javaabu\Auth\Tests\Models\Admin as BaseAdmin;
use Laravel\Sanctum\HasApiTokens;

class Admin extends BaseAdmin
{
    use HasApiTokens;

    protected static function newFactory(): AdminFactory
    {
        return new class extends AdminFactory
        {
            protected $model = Admin::class;
        };
    }
}
