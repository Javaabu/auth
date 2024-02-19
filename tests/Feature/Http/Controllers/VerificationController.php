<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Javaabu\Auth\Models\User;
use Javaabu\Auth\VerifiesEmails;
use function Laravel\Prompts\error;

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

    public function showEmailVerificationForm($request, $user, $message): View
    {
        return view('verification.verify')->with(compact('user', 'message'));
    }

    /**
     * Show verification result message
     *
     * @param  Request  $request
     * @param  null     $data
     * @param  null     $errors
     * @return Response|View
     */
    public function showVerificationResult(Request $request, $data = null, $errors = null)
    {
        return view('verification.result')
            ->with($data)
            ->withErrors($errors);
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath(): string
    {
        return with(new User())->emailVerificationRedirectUrl();
    }
}
