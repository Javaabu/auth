<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use App\Helpers\PasswordUpdate\UpdatesPassword;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Javaabu\Auth\Models\User;

class UpdatePasswordController extends \Javaabu\Auth\Http\Controllers\Auth\UpdatePasswordController
{
    /**
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware(['auth:web', 'active:web', 'password-update-required:web']);
    }

    /**
     * Define the guard
     *
     * @return mixed
     */
    protected function guard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    /**
     * The user broker
     */
    public function broker(): PasswordBroker
    {
        return Password::broker('users');
    }

    /**
     * Show the password update form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showPasswordUpdateForm(): \Illuminate\View\View
    {
        return view('passwords.update');
    }
}
