<?php

namespace Javaabu\Auth\Routing;

use Illuminate\Routing\Router;
use Javaabu\Auth\Policies\UserAccountPolicy;
use Javaabu\Auth\UserTypes;

/**
 * Registers the admin actions on one user type's accounts for `Route::userAccountActions('user', [...])`.
 * Put them behind the admins' middleware, e.g. `['web', 'auth.type:admin']`.
 */
class UserAccountRoutes
{
    /** The route action key that holds the managed user type */
    public const string ROUTE_ACTION = 'auth_managed_user_type';

    /**
     * @param  array{
     *     prefix?: string,
     *     name?: string,
     *     middleware?: string|array<int, string>
     * }  $options
     */
    public static function register(Router $router, string $type, array $options = []): void
    {
        $user_type = UserTypes::get($type);
        $resource = UserAccountPolicy::resource($user_type);
        $controller = config('javaabu-auth.controllers.user_accounts');
        $name = rtrim($options['name'] ?? "{$resource}.account", '.');

        $router->group([
            'prefix' => $options['prefix'] ?? $resource,
            'as' => "{$name}.",
            'middleware' => (array) ($options['middleware'] ?? ['web']),
            self::ROUTE_ACTION => $user_type->name(),
        ], function (Router $router) use ($controller) {
            $router->post('{account}/unlock', [$controller, 'unlock'])->name('unlock');
            $router->delete('{account}/two-factor', [$controller, 'resetTwoFactor'])->name('reset-two-factor');
            $router->post('{account}/require-password-update', [$controller, 'requirePasswordUpdate'])->name('require-password-update');
            $router->post('{account}/log-out', [$controller, 'logOut'])->name('log-out');
        });
    }
}
