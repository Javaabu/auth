<?php

namespace Javaabu\Auth\Http\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Javaabu\Auth\Tests\Feature\Models\User;

class RedirectIfEmailVerificationNotNeeded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /** @var User $user */
                $user = Auth::guard($guard)->user();

                if (! $user->needsEmailVerification()) {
                    if (expects_json($request)) {
                        abort(403, 'Users that do not need email verification not allowed');
                    }

                    return redirect()->to($user->emailVerificationRedirectUrl());
                }
            }
        }

        return $next($request);
    }
}
