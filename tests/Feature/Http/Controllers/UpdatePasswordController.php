<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class UpdatePasswordController extends \Javaabu\Auth\Http\Controllers\Auth\UpdatePasswordController
{
    public function applyMiddlewares(): void
    {
        $this->middleware(['auth:web', 'active:web', 'password-update-required:web']);
    }

    public function getGuard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    public function getBroker(): PasswordBroker
    {
        return Password::broker('users');
    }

    public function getPasswordUpdateForm(): \Illuminate\View\View
    {
        return view('passwords.update');
    }
}
