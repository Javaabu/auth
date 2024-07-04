<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Javaabu\Auth\Tests\Feature\Models\User;

class UpdatePasswordController extends \Javaabu\Auth\Http\Controllers\Auth\UpdatePasswordController
{
    public function getBroker(): PasswordBroker
    {
        return Password::broker('users');
    }

    public function getPasswordUpdateForm(): \Illuminate\View\View
    {
        return view('passwords.update');
    }

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User
    {
        return new User();
    }
}
