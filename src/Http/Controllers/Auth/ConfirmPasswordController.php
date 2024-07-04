<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\Auth\Contracts\ConfirmPasswordContract;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class ConfirmPasswordController extends AuthBaseController implements ConfirmPasswordContract
{
    use ConfirmsPasswords;
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof ConfirmsPasswords;
    }

    /**
     * Where to redirect users when the intended url fails.
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
     */
    public function showConfirmForm(): View
    {
        return $this->getConfirmForm();
    }

    /**
     * Reset the password confirmation timeout.
     *
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
     */
    public function resetPasswordConfirmationTimeoutKey(): string
    {
        return $this->guardName() . '.auth.password_confirmed_at';
    }

    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('auth:' . $this->guardName());
    }
}
