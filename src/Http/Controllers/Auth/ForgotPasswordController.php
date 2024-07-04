<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Javaabu\Auth\Contracts\ForgotPasswordContract;
use Javaabu\Auth\Http\Controllers\AuthBaseController;

abstract class ForgotPasswordController extends AuthBaseController implements ForgotPasswordContract
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
    public function broker(): PasswordBroker
    {
        return $this->getBroker();
    }

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm(): View
    {
        return $this->getPasswordResetForm();
    }
}
