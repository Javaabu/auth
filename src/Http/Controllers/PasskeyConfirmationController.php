<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Passkeys\VerifyPasskey;
use Laravel\Passkeys\Actions\GenerateVerificationOptions;
use Laravel\Passkeys\Http\Requests\PasskeyVerificationRequest;
use Laravel\Passkeys\Support\WebAuthn;

/**
 * Confirming the password with a passkey instead
 */
class PasskeyConfirmationController extends Controller
{
    public function options(Request $request, GenerateVerificationOptions $generate): JsonResponse
    {
        $options = $generate($this->user($request));

        $request->session()->put('passkey.verification_options', WebAuthn::toJson($options));

        return new JsonResponse([
            'options' => WebAuthn::toBrowserArray($options),
        ]);
    }

    public function store(PasskeyVerificationRequest $request, VerifyPasskey $verify): JsonResponse
    {
        $type = $this->userType($request);

        $verify($request->credential(), $request->verificationOptions(), $this->user($request));

        RequirePassword::markConfirmed($request, $type);

        return new JsonResponse([
            'redirect' => redirect()->getIntendedUrl() ?? $type->homeUrl($this->user($request)),
        ]);
    }
}
