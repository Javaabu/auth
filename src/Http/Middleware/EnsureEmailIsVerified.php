<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends users with an unverified email to the type's verification notice: `auth.verified:admin`.
 * Does nothing for types without the email_verification feature.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());
        $user = $user_type->auth()->user();

        if (! $user_type->hasFeature(UserType::EMAIL_VERIFICATION)) {
            return $next($request);
        }

        if (! $user instanceof MustVerifyEmail || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $user_type->hasRoute('verification.notice')) {
            abort(403, __('Your email address is not verified.'));
        }

        return redirect()->guest($user_type->route('verification.notice'));
    }
}
