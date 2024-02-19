<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends \Javaabu\Auth\Http\Controllers\Auth\ForgotPasswordController
{
    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     *
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker(): PasswordBroker
    {
        return Password::broker('users');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return View
     */
    public function showLinkRequestForm(): View
    {
        return view('passwords.email');
    }
}
