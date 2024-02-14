<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class ConfirmPasswordController extends AuthBaseController
{
    use ConfirmsPasswords;
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof ConfirmsPasswords;
    }

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected string $redirectTo = '/';

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
     * Display the password confirmation view.
     *
     * @return View
     */
    abstract public function showConfirmForm(): View;

    /**
     * Reset the password confirmation timeout.
     *
     * @param  Request  $request
     * @return void
     */
    protected function resetPasswordConfirmationTimeout(Request $request)
    {
        $request->session()->put($this->resetPasswordConfirmationTimeoutKey(), time());
    }

    /**
     * Get the password confirmation validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'password' => 'required|current_password',
        ];
    }

    /**
     * Get the password confirmation timeout key.
     *
     * @return string
     */
    public function resetPasswordConfirmationTimeoutKey(): string
    {
        return 'web_admin.auth.password_confirmed_at';
    }

    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     *
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('auth:web_admin');
    }
}
