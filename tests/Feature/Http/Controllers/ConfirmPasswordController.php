<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\Auth\Tests\Feature\Models\User;

class ConfirmPasswordController extends \Javaabu\Auth\Http\Controllers\Auth\ConfirmPasswordController
{
    /**
     * Display the password confirmation view.
     */
    public function getConfirmForm(): View
    {
        return view('passwords.confirm');
    }

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User
    {
        return new User();
    }
}
