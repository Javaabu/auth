<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends \Javaabu\Auth\Http\Controllers\Auth\ResetPasswordController
{
    /**
     * Define the password broker
     *
     * @return PasswordBroker
     */
    public function broker(): PasswordBroker
    {
        return Password::broker('users');
    }

    /**
     * Define the guard
     *
     * @return mixed
     */
    protected function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard('web');
    }

    public function getResetFormViewName(): string
    {
        return 'passwords.reset';
    }
}
