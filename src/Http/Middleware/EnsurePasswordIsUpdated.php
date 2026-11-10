<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\RequiresPasswordUpdate;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends users who must choose a new password to the type's new password page: `auth.password-updated:admin`
 */
class EnsurePasswordIsUpdated
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());
        $user = $user_type->auth()->user();

        if (! $user instanceof RequiresPasswordUpdate || ! $user->requiresPasswordUpdate()) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $user_type->hasRoute('password.required')) {
            abort(403, __('You need to choose a new password.'));
        }

        return redirect()->guest($user_type->route('password.required'));
    }
}
