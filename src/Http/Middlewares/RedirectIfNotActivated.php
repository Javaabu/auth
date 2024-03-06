<?php

namespace Javaabu\Auth\Http\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Javaabu\Auth\Models\User;

class RedirectIfNotActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  ...$guards
     * @return mixed
     *
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /** @var User $user */
                $user = Auth::guard($guard)->user();

                if (! $user->is_active) {
                    if (expects_json($request)) {
                        throw new AuthorizationException('Account not activated');
                    }

                    return redirect()->to($user->inactiveNoticeUrl());
                }
            }
        }

        return $next($request);
    }
}
