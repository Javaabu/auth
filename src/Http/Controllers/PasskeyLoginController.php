<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\Auth\Passkeys\VerifyPasskey;
use Laravel\Passkeys\Actions\GenerateVerificationOptions;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Http\Requests\PasskeyVerificationRequest;
use Laravel\Passkeys\Passkeys;
use Laravel\Passkeys\Support\WebAuthn;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logging in with a passkey, without a username or password. A passkey is already
 * two factors (the device and the user's fingerprint, face or PIN), so there's no code to enter.
 */
class PasskeyLoginController extends Controller
{
    public function options(Request $request, GenerateVerificationOptions $generate): JsonResponse
    {
        $options = $generate();

        $request->session()->put('passkey.verification_options', WebAuthn::toJson($options));

        return new JsonResponse([
            'options' => WebAuthn::toBrowserArray($options),
        ]);
    }

    public function store(PasskeyVerificationRequest $request, VerifyPasskey $verify, StartAuthenticatedSession $start_session): Response
    {
        $type = $this->userType($request);

        $passkey = $verify($request->credential(), $request->verificationOptions());

        $user = $passkey->user;

        // A passkey of another user type can't log in here
        if (! $type->owns($user)) {
            throw InvalidPasskeyException::make(__('Passkey not recognized. It may have been removed from your account.'));
        }

        if (! Passkeys::allowsLogin($request, $passkey)) {
            throw InvalidPasskeyException::make(__('Unable to sign in with this account.'));
        }

        return $start_session($request, $type, $user, $request->remember(), skip_two_factor: true);
    }
}
