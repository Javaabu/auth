<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends users who are already signed in on the type's guard to its home: `auth.guest:admin`
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());

        if ($user_type->auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['message' => __('You are already signed in.')], 409)
                : redirect()->to($user_type->homeUrl($user_type->auth()->user()));
        }

        return $next($request);
    }
}
