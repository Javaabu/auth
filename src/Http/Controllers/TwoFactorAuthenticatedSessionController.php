<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Actions\LoginUser;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use Javaabu\Auth\Events\TwoFactorAuthenticationFailed;
use Javaabu\Auth\Http\Responses\LoginResponse;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Auth\UserType;
use Symfony\Component\HttpFoundation\Response;

/**
 * The second step of a login: a code from the authenticator app, or a recovery code
 */
class TwoFactorAuthenticatedSessionController extends Controller
{
    public function create(Request $request): View|Responsable|RedirectResponse
    {
        $type = $this->userType($request);

        if (! $this->challengedUser($request, $type)) {
            return redirect()->to($type->route('login'));
        }

        return $this->render($request, 'two-factor-challenge');
    }

    public function store(Request $request, TwoFactorProvider $provider, LoginUser $login): Response
    {
        $type = $this->userType($request);
        $user = $this->challengedUser($request, $type);

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => __('Your login has expired. Please log in again.'),
            ]);
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request, $type, $user);

        if (! $this->passes($request, $provider, $user)) {
            RateLimiter::hit($this->throttleKey($type, $user));

            TwoFactorAuthenticationFailed::dispatch($user);

            $field = $request->filled('recovery_code') ? 'recovery_code' : 'code';

            throw ValidationException::withMessages([
                $field => $field === 'code'
                    ? __('The provided two-factor authentication code was invalid.')
                    : __('The provided two-factor recovery code was invalid.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($type, $user));

        $remember = (bool) ($request->session()->get(StartAuthenticatedSession::CHALLENGE_SESSION_KEY)['remember'] ?? false);

        $login($request, $type, $user, $remember);

        return app(LoginResponse::class)->toResponse($request);
    }

    protected function passes(Request $request, TwoFactorProvider $provider, TwoFactorAuthenticatable $user): bool
    {
        if ($request->filled('recovery_code')) {
            $code = trim((string) $request->input('recovery_code'));

            foreach ($user->recoveryCodes() as $recovery_code) {
                if (hash_equals($recovery_code, $code)) {
                    $user->replaceRecoveryCode($recovery_code);

                    return true;
                }
            }

            return false;
        }

        $secret = $user->twoFactorSecret();

        return $secret && $request->filled('code') && $provider->verify($secret, (string) $request->input('code'));
    }

    /**
     * The user waiting for their second step, if they belong to this user type
     */
    protected function challengedUser(Request $request, UserType $type): ?Authenticatable
    {
        $challenge = $request->session()->get(StartAuthenticatedSession::CHALLENGE_SESSION_KEY);

        if (! $challenge || ($challenge['user_type'] ?? null) !== $type->name()) {
            return null;
        }

        $user = $type->auth()->getProvider()->retrieveById($challenge['id']);

        return $user instanceof TwoFactorAuthenticatable ? $user : null;
    }

    protected function ensureIsNotRateLimited(Request $request, UserType $type, Authenticatable $user): void
    {
        $key = $this->throttleKey($type, $user);

        if (! RateLimiter::tooManyAttempts($key, (int) config('javaabu-auth.rate_limits.two_factor', 5))) {
            return;
        }

        throw ValidationException::withMessages([
            'code' => __('Too many attempts. Please try again in :seconds seconds.', [
                'seconds' => RateLimiter::availableIn($key),
            ]),
        ])->status(429);
    }

    protected function throttleKey(UserType $type, Authenticatable $user): string
    {
        return "auth.two_factor|{$type->name()}|{$user->getAuthIdentifier()}";
    }
}
