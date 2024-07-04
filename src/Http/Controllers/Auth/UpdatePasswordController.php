<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\View\View;
use Javaabu\Auth\Contracts\UpdatePasswordContract;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\PasswordUpdate\UpdatesPassword;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class UpdatePasswordController extends AuthBaseController implements UpdatePasswordContract
{
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof UpdatesPassword;
    }
    use UpdatesPassword;

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

    public function applyMiddlewares(): void
    {
        $this->middleware([
            'auth:' . $this->guardName(),
            'active:' . $this->guardName(),
            'password-update-required:' . $this->guardName()
        ]);
    }

    /**
     * Define the guard
     */
    protected function guard(): StatefulGuard
    {
        return $this->getGuard();
    }

    /**
     * The user broker
     */
    public function broker(): PasswordBroker
    {
        return $this->getBroker();
    }

    /**
     * Show the password update form
     */
    public function showPasswordUpdateForm(): View
    {
        return $this->getPasswordUpdateForm();
    }
}
