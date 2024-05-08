<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;

interface UpdatePasswordContract
{
    public function applyMiddlewares(): void;

    public function getGuard(): StatefulGuard;

    public function getBroker(): PasswordBroker;

    public function getPasswordUpdateForm(): View;

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User;
}
