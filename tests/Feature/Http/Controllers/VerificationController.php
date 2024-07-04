<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\View\View;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Auth\VerifiesEmails;

class VerificationController extends \Javaabu\Auth\Http\Controllers\Auth\VerificationController
{
    public function getEmailVerificationView(): View
    {
        return view('verification.verify');
    }

    public function getVerificationResultView()
    {
        return view('verification.result');
    }

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User
    {
        return new User();
    }
}
