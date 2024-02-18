<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Models\User;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;
use Javaabu\Auth\VerifiesEmails;

abstract class VerificationController extends AuthBaseController
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof VerifiesEmails;
    }

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->applyMiddlewares();
    }

    /**
     * Show verified message
     *
     * @param  Request  $request
     * @param  User     $user
     * @param  string   $message
     * @return Response|View
     */
    abstract public function showEmailVerificationForm(Request $request, User $user, $message);

    /**
     * Show verification result message
     *
     * @param  Request  $request
     * @param  null     $data
     * @param  null     $errors
     * @return Response|View
     */
    abstract public function showVerificationResult(Request $request, $data = null, $errors = null);

    public function applyMiddlewares(): void
    {
        $this->middleware('auth:web');
        $this->middleware('inactive:web')->except('verify');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
        $this->middleware('needs-verification')->except('show');
    }
}
