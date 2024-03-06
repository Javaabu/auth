<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\View\View;
use Javaabu\Auth\Models\User;
use Javaabu\Auth\VerifiesEmails;

class VerificationController extends \Javaabu\Auth\Http\Controllers\Auth\VerificationController
{
    use VerifiesEmails;

    public function applyMiddlewares(): void
    {
        $this->middleware('auth:web');
        $this->middleware('inactive:web')->except('verify');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
        $this->middleware('needs-verification')->except('show');
    }

    public function getEmailVerificationView(): View
    {
        return view('verification.verify');
    }

    public function getVerificationResultView()
    {
        return view('verification.result');
    }

    /**
     * Get the post register / login redirect path.
     */
    public function redirectPath(): string
    {
        return with(new User())->emailVerificationRedirectUrl();
    }
}
