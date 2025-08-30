<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\View\View;

interface LoginContract extends HasUserTypeRedirectContract
{
    public function applyMiddlewares(): void;

    public function getLoginForm();
}
