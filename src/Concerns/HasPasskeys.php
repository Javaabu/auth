<?php

namespace Javaabu\Auth\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Passkeys\Passkeys;

/**
 * Passkeys (laravel/passkeys) for any user type. Implement Laravel\Passkeys\Contracts\PasskeyUser.
 */
trait HasPasskeys
{
    use PasskeyAuthenticatable {
        PasskeyAuthenticatable::passkeys as basePasskeys;
    }

    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkeys::passkeyModel(), 'user_id')
            ->withAttributes(['user_type' => $this->getMorphClass()]);
    }

    public static function bootHasPasskeys(): void
    {
        static::deleted(function ($user) {
            if (method_exists($user, 'isForceDeleting') && ! $user->isForceDeleting()) {
                return;
            }

            $user->passkeys()->delete();
        });
    }
}
