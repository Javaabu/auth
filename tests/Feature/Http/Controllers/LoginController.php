<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Javaabu\Auth\Contracts\LoginControllerContract;
use Javaabu\Auth\Http\Controllers\Auth\LoginController as JavaabuLoginController;

class LoginController extends JavaabuLoginController implements LoginControllerContract
{
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web')->except('logout');
    }

    public function getGuard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    public function getLoginForm(): View
    {
        return view('login');
    }
}
