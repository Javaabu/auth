<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Actions\TwoFactor\ConfirmTwoFactorAuthentication;
use Javaabu\Auth\Actions\TwoFactor\DisableTwoFactorAuthentication;
use Javaabu\Auth\Actions\TwoFactor\EnableTwoFactorAuthentication;
use Javaabu\Auth\Actions\TwoFactor\GenerateNewRecoveryCodes;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turning two-factor authentication on and off, from the security page
 */
class TwoFactorAuthenticationController extends Controller
{
    public function store(Request $request, EnableTwoFactorAuthentication $enable): Response
    {
        $enable($this->twoFactorUser($request), $request->boolean('force'));

        return $this->respond($request, 'two-factor-authentication-enabled', __('Scan the QR code with your authenticator app, then enter the code it shows.'));
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): Response
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $confirm($this->twoFactorUser($request), (string) $request->input('code'));

        return $this->respond($request, 'two-factor-authentication-confirmed', __('Two-factor authentication is now on.'));
    }

    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): Response
    {
        if ($this->userType($request)->requiresTwoFactor()) {
            abort(403, __('Two-factor authentication is required for your account.'));
        }

        $disable($this->twoFactorUser($request));

        return $this->respond($request, 'two-factor-authentication-disabled', __('Two-factor authentication is now off.'));
    }

    public function qrCode(Request $request): JsonResponse
    {
        $user = $this->twoFactorUser($request);

        if (is_null($user->two_factor_secret)) {
            return new JsonResponse([]);
        }

        return new JsonResponse([
            'svg' => $user->twoFactorQrCodeSvg(),
            'url' => $user->twoFactorQrCodeUrl(),
        ]);
    }

    public function secretKey(Request $request): JsonResponse
    {
        $user = $this->twoFactorUser($request);

        if (is_null($user->two_factor_secret)) {
            abort(404, __('Two-factor authentication has not been enabled.'));
        }

        return new JsonResponse(['secret_key' => $user->twoFactorSecret()]);
    }

    public function recoveryCodes(Request $request): JsonResponse
    {
        $user = $this->twoFactorUser($request);

        if (is_null($user->two_factor_secret) || is_null($user->two_factor_recovery_codes)) {
            return new JsonResponse([]);
        }

        return new JsonResponse($user->recoveryCodes());
    }

    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): Response
    {
        $generate($this->twoFactorUser($request));

        return $this->respond($request, 'recovery-codes-generated', __('New recovery codes have been generated.'));
    }

    protected function twoFactorUser(Request $request): TwoFactorAuthenticatable
    {
        $user = $this->user($request);

        if (! $user instanceof TwoFactorAuthenticatable) {
            throw new RuntimeException('Add the TwoFactorAuthenticatable trait and contract to ' . $user::class . '.');
        }

        return $user;
    }

    protected function respond(Request $request, string $status, string $message): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['status' => $status, 'message' => $message]);
        }

        return back()->with('status', $message)->with('two_factor_status', $status);
    }
}
