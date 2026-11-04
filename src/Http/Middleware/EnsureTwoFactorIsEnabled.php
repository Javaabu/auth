<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * For types that require two-factor authentication: sends users who haven't set it up to the
 * security page, the only page they can use until they do: `auth.two-factor:admin`
 */
class EnsureTwoFactorIsEnabled
{
    /** Routes users can reach before setting up two-factor authentication */
    public const array ALLOWED_ROUTES = [
        'security.edit',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.qr-code',
        'two-factor.secret-key',
        'two-factor.recovery-codes',
        'password.confirm',
        'password.confirm.store',
        'logout',
    ];

    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());
        $user = $user_type->auth()->user();

        if (! $user_type->requiresTwoFactor()) {
            return $next($request);
        }

        if (! $user instanceof TwoFactorAuthenticatable || $user->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        foreach (self::ALLOWED_ROUTES as $route) {
            if ($request->routeIs($user_type->routeNameFor($route))) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || ! $user_type->hasRoute('security.edit')) {
            abort(403, __('You need to set up two-factor authentication.'));
        }

        return redirect()->guest($user_type->route('security.edit'))
            ->with('status', __('Set up two-factor authentication to continue.'));
    }
}
