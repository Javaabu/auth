<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\Auth\RegisterController as JavaabuRegisterController;
use Javaabu\Auth\User;

class RegisterController extends JavaabuRegisterController
{
    /**
     * Display the registration form
     */
    public function showRegistrationForm(): View
    {
        return view('register');
    }

    public function determinePathForRedirectUsing(): User
    {
        return new \Javaabu\Auth\Tests\Feature\Models\User();
    }
}
