<?php

namespace Javaabu\Auth\Actions\TwoFactor;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Events\RecoveryCodesGenerated;
use Javaabu\Auth\TwoFactor\RecoveryCode;

class GenerateNewRecoveryCodes
{
    public function __invoke(Model $user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(static::codes())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }

    /** @return array<int, string> */
    public static function codes(): array
    {
        $count = (int) config('javaabu-auth.two_factor.recovery_codes', 8);

        return array_map(fn () => RecoveryCode::generate(), range(1, max($count, 1)));
    }
}
