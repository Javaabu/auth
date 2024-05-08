<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\Auth\RegisterController as JavaabuRegisterController;
use Javaabu\Auth\User;

class RegisterController extends JavaabuRegisterController
{
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web');
    }

    public function getGuard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    /**
     * Display the registration form
     */
    public function showRegistrationForm(): View
    {
        return view('register');
    }

    public function getLoginForm(): View
    {
        return view('login');
    }

    public function determinePathForRedirectUsing(): User
    {
        return new \Javaabu\Auth\Tests\Feature\Models\User();
    }

    public function userClass(): string
    {
        return \Javaabu\Auth\Tests\Feature\Models\User::class;
    }
}
