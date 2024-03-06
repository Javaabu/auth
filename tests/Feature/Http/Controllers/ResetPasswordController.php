<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends \Javaabu\Auth\Http\Controllers\Auth\ResetPasswordController
{
    public function getBroker(): PasswordBroker
    {
        return Password::broker('users');
    }

    public function getGuard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    public function getResetFormViewName(): string
    {
        return 'passwords.reset';
    }
}
