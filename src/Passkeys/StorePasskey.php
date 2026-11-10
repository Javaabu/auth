<?php

namespace Javaabu\Auth\Passkeys;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passkeys\Actions\StorePasskey as BaseStorePasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Passkey as BasePasskey;
use Webauthn\Exception\WebauthnException;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

/**
 * Failed WebAuthn checks while adding a passkey are validation errors, not server errors
 */
class StorePasskey extends BaseStorePasskey
{
    public function __invoke(Authenticatable $user, string $name, PublicKeyCredential $credential, PublicKeyCredentialCreationOptions $options): BasePasskey
    {
        try {
            return parent::__invoke($user, $name, $credential, $options);
        } catch (WebauthnException) {
            throw InvalidPasskeyException::make(__('Unable to register passkey. Please try again.'));
        }
    }
}
