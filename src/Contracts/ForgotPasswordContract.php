<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\View\View;

interface ForgotPasswordContract
{
    public function applyMiddlewares(): void;

    public function getBroker(): PasswordBroker;

    public function getPasswordResetForm();
}
