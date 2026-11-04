<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tells the package which user type a route belongs to: `auth.user-type:admin`
 */
class SetUserType
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());

        $request->attributes->set(UserTypeRegistry::ROUTE_ACTION, $type->name());

        return $next($request);
    }
}
