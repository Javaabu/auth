<?php

namespace Javaabu\Auth\PasswordUpdate\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Javaabu\Auth\PasswordUpdate\PasswordUpdatableContract;

class RedirectIfPasswordUpdateRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            /** @var PasswordUpdatableContract $user */
            $user = Auth::guard($guard)->user();

            if ($user->requiresPasswordUpdate()) {
                if (expects_json($request)) {
                    throw new AuthorizationException('Password update required');
                }

                return redirect()->to($user->passwordUpdateUrl());
            }
        }

        return $next($request);
    }
}
