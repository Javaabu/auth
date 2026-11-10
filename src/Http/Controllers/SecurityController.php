<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use Javaabu\Auth\Sessions\Session;
use Javaabu\Auth\Support\ApiTokens;
use Javaabu\Auth\UserType;

/**
 * The security page: password, two-factor authentication, passkeys, browser sessions and API tokens
 */
class SecurityController extends Controller
{
    public function edit(Request $request): View|Responsable
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        return $this->render($request, 'settings.security', [
            'two_factor' => $this->twoFactor($type, $user),
            'passkeys' => $this->passkeys($type, $user),
            'sessions' => $this->sessions($type, $user),
            'api_tokens' => $this->apiTokens($type, $user),
            'plain_text_token' => session('plain_text_token'),
        ], 'account');
    }

    /** @return array<string, mixed>|null */
    protected function twoFactor(UserType $type, mixed $user): ?array
    {
        if (! $type->hasTwoFactor() || ! $user instanceof TwoFactorAuthenticatable) {
            return null;
        }

        $pending = ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at);

        return [
            'enabled' => $user->hasEnabledTwoFactorAuthentication(),
            'pending' => $pending,
            'required' => $type->requiresTwoFactor(),
            'qr_code' => $pending ? $user->twoFactorQrCodeSvg() : null,
            'secret_key' => $pending ? $user->twoFactorSecret() : null,
            'recovery_codes' => session('two_factor_status') === 'two-factor-authentication-confirmed'
                || session('two_factor_status') === 'recovery-codes-generated'
                    ? $user->recoveryCodes()
                    : null,
        ];
    }

    /** @return array<int, array<string, mixed>>|null */
    protected function passkeys(UserType $type, mixed $user): ?array
    {
        if (! $type->hasFeature(UserType::PASSKEYS) || ! method_exists($user, 'passkeys')) {
            return null;
        }

        return $user->passkeys()
            ->latest()
            ->get()
            ->map(fn ($passkey) => [
                ...(method_exists($passkey, 'toDisplayArray') ? $passkey->toDisplayArray() : ['id' => $passkey->id, 'name' => $passkey->name]),
                'destroy_url' => $type->route('passkey.destroy', ['passkey_id' => $passkey->id]),
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>>|null */
    protected function sessions(UserType $type, mixed $user): ?array
    {
        if (! $type->hasFeature(UserType::SESSIONS) || config('session.driver') !== 'multi_auth_database' || ! method_exists($user, 'sessions')) {
            return null;
        }

        return $user->sessions()
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (Session $session) => $session->toDisplayArray())
            ->all();
    }

    /** @return array<int, array<string, mixed>>|null */
    protected function apiTokens(UserType $type, mixed $user): ?array
    {
        if (! $type->hasFeature(UserType::API_TOKENS) || ! $type->hasRoute('api-tokens.store') || ! ApiTokens::supports($user)) {
            return null;
        }

        return array_map(fn (array $token) => [
            ...$token,
            'destroy_url' => $type->route('api-tokens.destroy', ['token_id' => $token['id']]),
        ], ApiTokens::list($user));
    }
}
