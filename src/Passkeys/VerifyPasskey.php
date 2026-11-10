<?php

namespace Javaabu\Auth\Passkeys;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passkeys\Actions\VerifyPasskey as BaseVerifyPasskey;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Passkey as BasePasskey;
use Webauthn\Exception\WebauthnException;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Checks the user type as well as the id, so an admin's passkey can't confirm a public user with the same id
 */
class VerifyPasskey extends BaseVerifyPasskey
{
    /**
     * Failed WebAuthn checks (a bad signature, a wrong origin, …) are validation errors, not server errors
     */
    public function __invoke(PublicKeyCredential $credential, PublicKeyCredentialRequestOptions $options, ?PasskeyUser $user = null): BasePasskey
    {
        try {
            return parent::__invoke($credential, $options, $user);
        } catch (WebauthnException) {
            throw InvalidPasskeyException::make(__('Unable to verify passkey. Please try again.'));
        }
    }

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
