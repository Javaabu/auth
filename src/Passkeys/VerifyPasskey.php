<?php

namespace Javaabu\Auth\Passkeys;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passkeys\Actions\VerifyPasskey as BaseVerifyPasskey;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Passkey as BasePasskey;

/**
 * Checks the user type as well as the id, so an admin's passkey can't confirm a public user with the same id
 */
class VerifyPasskey extends BaseVerifyPasskey
{
    public function ensurePasskeyBelongsToUser(BasePasskey $passkey, ?PasskeyUser $user): void
    {
        parent::ensurePasskeyBelongsToUser($passkey, $user);

        if (! $user instanceof Model) {
            return;
        }

        if ($passkey->getAttribute('user_type') !== $user->getMorphClass()) {
            throw InvalidPasskeyException::make(__('Passkey not recognized. It may have been removed from your account.'));
        }
    }
}
