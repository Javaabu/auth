<?php

namespace Javaabu\Auth\Actions\TwoFactor;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Events\TwoFactorAuthenticationEnabled;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;

/**
 * Creates a secret and recovery codes; the user then confirms with a code from their app
 */
class EnableTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorProvider $provider,
        protected GenerateNewRecoveryCodes $recovery_codes,
    ) {}

    public function __invoke(Model $user, bool $force = false): void
    {
        if (! empty($user->two_factor_secret) && ! $force) {
            return;
        }

        $user->forceFill([
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(GenerateNewRecoveryCodes::codes())),
            'two_factor_confirmed_at' => null,
        ])->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
