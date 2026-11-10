<?php

namespace Javaabu\Auth\Contracts;

use Illuminate\Routing\Router;
use Javaabu\Auth\UserType;

/**
 * A way to log in besides the password form, such as a mobile number and a one-time code.
 *
 * Route::authRoutes() calls routes() inside the type's guest route group, and the login
 * page links to url(). Once the method has identified the user, it finishes the login with
 * Javaabu\Auth\Actions\StartAuthenticatedSession, which checks the account, runs the
 * two-factor challenge and starts the session.
 */
interface LoginMethod
{
    /** A unique key, e.g. `mobile` */
    public function key(): string;

    /** The text of the link on the login page */
    public function label(): string;

    public function routes(Router $router, UserType $type): void;

    /** Where the login page links to */
    public function url(UserType $type): string;
}
