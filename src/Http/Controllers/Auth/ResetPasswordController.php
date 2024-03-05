<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\Auth\Contracts\ResetPasswordContract;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class ResetPasswordController extends AuthBaseController implements ResetPasswordContract
{
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof ResetsPasswords;
    }
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     */
    protected string $redirectTo = '/';

    /**
     * Define the password broker
     */
    public function broker(): PasswordBroker
    {
        return $this->getBroker();
    }

    /**
     * Define the guard
     *
     * @return mixed
     */
    public function guard(): StatefulGuard
    {
        return $this->getGuard();
    }

    /**
     * Set the user's password.
     *
     * @param  CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function setUserPassword($user, $password)
    {
        $user->password = $password;
    }

    /**
     * Get the view name for the showResetForm method
     */
    abstract public function getResetFormViewName(): string;

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  string|null  $token
     * @return Factory|View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view($this->getResetFormViewName())->with(
            ['token' => $token, 'email' => $request->email]
        );
    }
}
