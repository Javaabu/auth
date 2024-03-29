<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\View\View;

interface RegisterContract
{
    public function showRegistrationForm(): View;

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User;
}
