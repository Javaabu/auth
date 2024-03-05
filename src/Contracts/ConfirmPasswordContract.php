<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\View\View;

interface ConfirmPasswordContract
{
    public function getConfirmForm(): View;
}
