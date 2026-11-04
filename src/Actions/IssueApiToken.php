<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use Javaabu\Auth\Support\AccountState;
use Javaabu\Auth\Support\ApiTokens;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Auth\UserType;

/**
 * Checks a username, password (and two-factor code) and creates an API token for the device
 */
class IssueApiToken
{
    public function __construct(
        protected TwoFactorProvider $two_factor,
        protected StartAuthenticatedSession $start_session,
    ) {}

    public function __invoke(Request $request, UserType $type): string
    {
        $username = $type->usernameColumn();
        $provider = $type->auth()->getProvider();
        $credentials = [
            $username => mb_strtolower(trim((string) $request->input($username))),
            'password' => (string) $request->input('password'),
        ];

        $user = $provider->retrieveByCredentials([$username => $credentials[$username]]);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            event(new Failed($type->guardName(), $user, $credentials));

            if ($user instanceof LocksAfterFailedLogins) {
                $user->incrementLoginAttempts();
            }

            throw ValidationException::withMessages([$username => __('These credentials do not match our records.')]);
        }

        $inactive = AccountState::inactiveMessage($user);

        if ($inactive !== null) {
            throw ValidationException::withMessages([$username => $inactive]);
        }

        if ($this->start_session->needsTwoFactorChallenge($type, $user)) {
            $this->checkTwoFactor($request, $user);
        }

        if ($user instanceof LocksAfterFailedLogins) {
            $user->resetLoginAttempts();
        }

        if (! ApiTokens::supports($user)) {
            throw ValidationException::withMessages([$username => __('API tokens are not available for this account.')]);
        }

        return ApiTokens::create($user, (string) $request->input('device_name'));
    }

    protected function checkTwoFactor(Request $request, TwoFactorAuthenticatable $user): void
    {
        if ($request->filled('recovery_code')) {
            foreach ($user->recoveryCodes() as $code) {
                if (hash_equals($code, (string) $request->input('recovery_code'))) {
                    $user->replaceRecoveryCode($code);

                    return;
                }
            }

            throw ValidationException::withMessages(['recovery_code' => __('The provided two-factor recovery code was invalid.')]);
        }

        if (! $request->filled('code')) {
            throw ValidationException::withMessages(['code' => __('Enter the code from your authenticator app.')]);
        }

        if (! $this->two_factor->verify((string) $user->twoFactorSecret(), (string) $request->input('code'))) {
            throw ValidationException::withMessages(['code' => __('The provided two-factor authentication code was invalid.')]);
        }
    }
}
