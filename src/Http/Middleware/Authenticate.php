<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Everything a signed-in page of a user type needs, in one middleware: `auth.type:admin`.
 * Signed in on the type's guard, then (unless turned off with a second parameter `basic`):
 * an active account, a verified email, no required password change and two-factor set up when required.
 */
class Authenticate
{
    public function handle(Request $request, Closure $next, ?string $type = null, ?string $mode = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());

        $request->attributes->set(UserTypeRegistry::ROUTE_ACTION, $user_type->name());

        $this->authenticate($request, $user_type);

        if ($mode === 'basic') {
            return $next($request);
        }

        $checks = [
            EnsureAccountIsActive::class,
            EnsureEmailIsVerified::class,
            EnsurePasswordIsUpdated::class,
            EnsureTwoFactorIsEnabled::class,
        ];

        $pipeline = array_reduce(
            array_reverse($checks),
            fn (Closure $next, string $check) => fn (Request $request) => app($check)->handle($request, $next, $user_type->name()),
            $next,
        );

        return $pipeline($request);
    }

    protected function authenticate(Request $request, UserType $type): void
    {
        $guard = $type->auth();

        if ($guard->check()) {
            auth()->shouldUse($type->guardName());

            return;
        }

        throw new AuthenticationException(
            'Unauthenticated.',
            [$type->guardName()],
            $request->expectsJson() || ! $type->hasRoute('login') ? null : $type->route('login'),
        );
    }
}
