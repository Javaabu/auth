<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Passkeys\Passkey;
use Laravel\Passkeys\Actions\DeletePasskey;
use Laravel\Passkeys\Actions\GenerateRegistrationOptions;
use Laravel\Passkeys\Actions\StorePasskey;
use Laravel\Passkeys\Http\Requests\PasskeyRegistrationRequest;
use Laravel\Passkeys\Passkeys;
use Laravel\Passkeys\Support\WebAuthn;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adding and removing passkeys, from the security page
 */
class PasskeysController extends Controller
{
    public function options(Request $request, GenerateRegistrationOptions $generate): JsonResponse
    {
        $options = $generate($this->user($request));

        $request->session()->put('passkey.registration_options', WebAuthn::toJson($options));

        return new JsonResponse([
            'options' => WebAuthn::toBrowserArray($options),
        ]);
    }

    public function store(PasskeyRegistrationRequest $request, StorePasskey $store): JsonResponse
    {
        $passkey = $store(
            $this->user($request),
            $request->string('name')->toString(),
            $request->credential(),
            $request->registrationOptions(),
        );

        return new JsonResponse([
            'message' => __('Passkey added.'),
            'passkey' => $passkey instanceof Passkey ? $passkey->toDisplayArray() : ['id' => $passkey->id, 'name' => $passkey->name],
        ], 201);
    }

    public function destroy(Request $request, string $passkey_id, DeletePasskey $delete): Response
    {
        $user = $this->user($request);

        $model = $user->passkeys()->whereKey($passkey_id)->firstOrFail();

        $delete($user, $model);

        $message = __('Passkey removed.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
