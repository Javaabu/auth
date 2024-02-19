<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Models\User;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;
use Javaabu\Auth\User as UserContract;

abstract class ResetPasswordController extends AuthBaseController
{
    use ResetsPasswords;
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof ResetsPasswords;
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';


    /**
     * Define the password broker
     *
     * @return PasswordBroker
     */
    abstract public function broker(): PasswordBroker;

    /**
     * Define the guard
     *
     * @return mixed
     */
    abstract protected function guard(): StatefulGuard;

    /**
     * Set the user's password.
     *
     * @param  CanResetPassword  $user
     * @param  string            $password
     * @return void
     */
    protected function setUserPassword($user, $password)
    {
        $user->password = $password;
    }

    /**
     * Get the view name for the showResetForm method
     *
     * @return string
     */
    abstract public function getResetFormViewName(): string;

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  Request      $request
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
