<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asks for the password again before sensitive pages: `auth.password.confirm:admin`.
 * Each user type keeps its own confirmation, so confirming as a public user doesn't
 * unlock admin pages in the same browser.
 */
class RequirePassword
{
    public function handle(Request $request, Closure $next, ?string $type = null, ?int $timeout = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());

        if (static::isConfirmed($request, $user_type, $timeout)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Password confirmation required.')], 423);
        }

        return redirect()->guest($user_type->route('password.confirm'));
    }

    public static function sessionKey(UserType $type): string
    {
        return "auth.password_confirmed_at.{$type->name()}";
    }

    public static function markConfirmed(Request $request, UserType $type): void
    {
        $request->session()->put(static::sessionKey($type), time());
    }

    public static function isConfirmed(Request $request, UserType $type, ?int $timeout = null): bool
    {
        $timeout ??= (int) config('javaabu-auth.password_timeout', 10800);
        $confirmed_at = (int) $request->session()->get(static::sessionKey($type), 0);

        return (time() - $confirmed_at) < $timeout;
    }
}
