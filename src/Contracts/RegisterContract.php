<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;

interface RegisterContract
{
    public function applyMiddlewares(): void;

    public function getGuard(): StatefulGuard;

    public function showRegistrationForm();

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User;

    public function userClass(): string;
}
