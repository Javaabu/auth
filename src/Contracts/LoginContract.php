<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;
use Javaabu\Auth\User;

interface LoginContract
{
    public function getGuard(): StatefulGuard;

    public function getLoginForm(): View;

    public function determinePathForRedirectUsing(): User;
}
