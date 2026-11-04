<?php

namespace Javaabu\Auth\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Javaabu\Auth\Support\AccountState;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops pending, banned and locked accounts: `auth.active:admin`
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());
        $message = AccountState::inactiveMessage($user_type->auth()->user());

        if ($message === null) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $user_type->hasRoute('account.inactive')) {
            throw new AuthorizationException($message);
        }

        return redirect()->to($user_type->route('account.inactive'));
    }
}
