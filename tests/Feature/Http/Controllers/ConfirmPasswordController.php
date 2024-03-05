<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfirmPasswordController extends \Javaabu\Auth\Http\Controllers\Auth\ConfirmPasswordController
{
    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('auth:web');
    }

    /**
     * Display the password confirmation view.
     */
    public function getConfirmForm(): View
    {
        return view('passwords.confirm');
    }

    /**
     * Reset the password confirmation timeout.
     *
     * @return void
     */
    protected function resetPasswordConfirmationTimeout(Request $request)
    {
        $request->session()->put('web.auth.password_confirmed_at', time());
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
}
