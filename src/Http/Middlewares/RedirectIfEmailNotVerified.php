<?php

namespace Javaabu\Auth\Http\Middlewares;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;

class RedirectIfEmailNotVerified
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                !$request->user()->hasVerifiedEmail())) {

            if (expects_json($request)) {
                abort(403, 'Your email address is not verified.');
            }

            return Redirect::to($request->user()->inactiveNoticeUrl());
        }

        return $next($request);
    }
}
