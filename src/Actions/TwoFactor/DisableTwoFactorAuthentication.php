<?php

namespace Javaabu\Auth\Actions\TwoFactor;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Events\TwoFactorAuthenticationDisabled;

class DisableTwoFactorAuthentication
{
    public function __invoke(Model $user): void
    {
        if (is_null($user->two_factor_secret) && is_null($user->two_factor_recovery_codes) && is_null($user->two_factor_confirmed_at)) {
            return;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        TwoFactorAuthenticationDisabled::dispatch($user);
    }
}
