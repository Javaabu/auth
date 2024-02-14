<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class LoginController extends AuthBaseController
{
    use AuthenticatesUsers;
    use DeterminesRedirectPaths {
        DeterminesRedirectPaths::redirectPath insteadof AuthenticatesUsers;
    }

    /**
     * Where to redirect users after login.
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
     * Show the application's login form.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return $this->getLoginForm();
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return StatefulGuard
     */
    public function guard(): StatefulGuard
    {
        return $this->getGuard();
    }

    /**
     * The user has logged out of the application.
     *
     * @param  Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->to($this->redirectPath());
    }

    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     *
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web_admin')->except('logout');
    }
}
