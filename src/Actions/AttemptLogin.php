<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins;
use Javaabu\Auth\Events\AccountLocked;
use Javaabu\Auth\UserType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks a username and password, then hands over to StartAuthenticatedSession
 */
class AttemptLogin
{
    public function __construct(
        protected StartAuthenticatedSession $start_session,
        protected Timebox $timebox,
    ) {}

    public function __invoke(Request $request, UserType $type): Response
    {
        $this->ensureIsNotRateLimited($request, $type);

        $user = $this->timebox->call(function (Timebox $timebox) use ($request, $type) {
            $user = $this->validateCredentials($request, $type);

            if ($user) {
                $timebox->returnEarly();
            }

            return $user;
        }, 200 * 1000);

        if (! $user) {
            $this->failed($request, $type);
        }

        RateLimiter::clear($this->throttleKey($request, $type));

        return ($this->start_session)($request, $type, $user, $request->boolean('remember'));
    }

    protected function validateCredentials(Request $request, UserType $type): ?Authenticatable
    {
        $provider = $type->auth()->getProvider();
        $credentials = $this->credentials($request, $type);

        $user = $provider->retrieveByCredentials([$type->usernameColumn() => $credentials[$type->usernameColumn()]]);

        if ($user instanceof LocksAfterFailedLogins && $user->isLockedOut()) {
            throw ValidationException::withMessages([
                $type->usernameColumn() => __('Your account has been locked due to too many failed logins. Reset your password to unlock it.'),
            ]);
        }

        if ($user && $provider->validateCredentials($user, $credentials)) {
            return $user;
        }

        event(new Failed($type->guardName(), $user, $credentials));

        if ($user instanceof LocksAfterFailedLogins) {
            $user->incrementLoginAttempts();

            if ($user->isLockedOut()) {
                AccountLocked::dispatch($user);
            }
        }

        return null;
    }

    /** @return array<string, string> */
    protected function credentials(Request $request, UserType $type): array
    {
        return [
            $type->usernameColumn() => mb_strtolower(trim((string) $request->input($type->usernameColumn()))),
            'password' => (string) $request->input('password'),
        ];
    }

    protected function failed(Request $request, UserType $type): never
    {
        RateLimiter::hit($this->throttleKey($request, $type));

        throw ValidationException::withMessages([
            $type->usernameColumn() => __('These credentials do not match our records.'),
        ]);
    }

    protected function ensureIsNotRateLimited(Request $request, UserType $type): void
    {
        $key = $this->throttleKey($request, $type);

        if (! RateLimiter::tooManyAttempts($key, (int) config('javaabu-auth.rate_limits.login', 5))) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            $type->usernameColumn() => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])->status(429);
    }

    protected function throttleKey(Request $request, UserType $type): string
    {
        $username = Str::transliterate(Str::lower((string) $request->input($type->usernameColumn())));

        return "auth.login|{$type->name()}|{$username}|{$request->ip()}";
    }
}
