<?php

namespace Javaabu\Auth\Routing;

use Illuminate\Routing\Router;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;

/**
 * Registers a user type's token endpoints for `Route::authApiRoutes('user', [...])` (Sanctum):
 * `POST {prefix}/tokens` to log in and get a token, `DELETE {prefix}/tokens/current` to log out
 */
class AuthApiRoutes
{
    /**
     * @param  array{
     *     prefix?: string,
     *     middleware?: string|array<int, string>,
     *     auth_middleware?: string|array<int, string>
     * }  $options
     */
    public static function register(Router $router, ?string $type = null, array $options = []): void
    {
        $user_type = $type ? UserTypes::get($type) : UserTypes::default();
        $controller = config('javaabu-auth.controllers.api_token_login');

        $router->group([
            'prefix' => $options['prefix'] ?? 'api',
            'as' => "{$user_type->routeNamePrefix()}api.",
            'middleware' => (array) ($options['middleware'] ?? ['api']),
            UserTypeRegistry::ROUTE_ACTION => $user_type->name(),
        ], function (Router $router) use ($controller, $options) {
            $router->post('tokens', [$controller, 'store'])->middleware('throttle:auth.api-tokens')->name('tokens.store');
            $router->delete('tokens/current', [$controller, 'destroy'])
                ->middleware((array) ($options['auth_middleware'] ?? ['auth:sanctum']))
                ->name('tokens.destroy');
        });
    }
}
