<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\Auth\LoginController as JavaabuLoginController;
use Javaabu\Auth\User;

class LoginController extends JavaabuLoginController
{
    public function getLoginForm(): View
    {
        return view('login');
    }

    public function determinePathForRedirectUsing(): User
    {
        return new \Javaabu\Auth\Tests\Feature\Models\User();
    }
}
