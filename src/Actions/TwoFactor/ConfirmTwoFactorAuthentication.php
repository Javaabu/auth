<?php

namespace Javaabu\Auth\Actions\TwoFactor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Events\TwoFactorAuthenticationConfirmed;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;

class ConfirmTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorProvider $provider,
    ) {}

    public function __invoke(Model $user, string $code): void
    {
        $secret = $user->twoFactorSecret();

        if (! $secret || ! $code || ! $this->provider->verify($secret, $code)) {
            throw ValidationException::withMessages([
                'code' => __('The provided two-factor authentication code was invalid.'),
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
