<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\AuthBaseController;

abstract class ForgotPasswordController extends AuthBaseController
{
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->applyMiddlewares();
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    abstract public function broker(): PasswordBroker;

    /**
     * Display the form to request a password reset link.
     *
     * @return View
     */
    abstract public function showLinkRequestForm(): View;

    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     *
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web_admin');
    }
}
