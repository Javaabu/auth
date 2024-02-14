<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\PasswordUpdate\UpdatesPassword;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class UpdatePasswordController extends AuthBaseController
{
    use UpdatesPassword;
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof UpdatesPassword;
    }

    /**
     * Where to redirect users after updating the password.
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
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware(['auth:web_admin', 'active:web_admin', 'password-update-required:web_admin']);
    }

    /**
     * Define the guard
     *
     * @return StatefulGuard
     */
    abstract protected function guard(): StatefulGuard;

    /**
     * The user broker
     */
    abstract public function broker(): PasswordBroker;

    /**
     * Show the password update form
     *
     * @return View
     */
    abstract public function showPasswordUpdateForm(): View;
}
