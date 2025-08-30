<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\View\View;

interface ConfirmPasswordContract extends HasUserTypeRedirectContract
{
    public function applyMiddlewares(): void;

    public function getConfirmForm();
}
