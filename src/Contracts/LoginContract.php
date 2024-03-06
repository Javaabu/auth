<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;

interface LoginContract
{
    public function getGuard(): StatefulGuard;

    public function getLoginForm(): View;
}
