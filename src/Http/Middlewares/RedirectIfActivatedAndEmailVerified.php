<?php

namespace Javaabu\Auth\Http\Middlewares;

use Javaabu\Auth\User;
use Closure;
use Illuminate\Support\Facades\Auth;
class RedirectIfActivatedAndEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed ...$guards
     * @return mixed
     */
	public function handle($request, Closure $next, ...$guards)
	{
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /** @var User $user */
                $user = Auth::guard($guard)->user();

                if ($user->is_active && $user->hasVerifiedEmail()) {
                    if (expects_json($request)) {
                        abort(403, 'Active users with verified emails not allowed');
                    }

                    return redirect()->to($user->homeUrl());
                }
            }
        }

	    return $next($request);
	}
}
