<?php

namespace Javaabu\Auth\Concerns;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Javaabu\Auth\Events\RecoveryCodeReplaced;
use Javaabu\Auth\TwoFactor\RecoveryCode;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;

/**
 * TOTP two-factor authentication, in the same columns and format as laravel/fortify:
 * `two_factor_secret`, `two_factor_recovery_codes` (both encrypted) and `two_factor_confirmed_at`
 */
trait TwoFactorAuthenticatable
{
    public function initializeTwoFactorAuthenticatable(): void
    {
        $this->mergeCasts(['two_factor_confirmed_at' => 'datetime']);

        $this->makeHidden(['two_factor_secret', 'two_factor_recovery_codes']);
    }

    /**
     * Turned on and confirmed with a code from the app
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret)
            && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Turned on but not confirmed yet
     */
    public function hasPendingTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret)
            && is_null($this->two_factor_confirmed_at);
    }

    public function twoFactorSecret(): ?string
    {
        return $this->two_factor_secret
            ? decrypt($this->two_factor_secret)
            : null;
    }

    /** @return array<int, string> */
    public function recoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?: [];
    }

    public function replaceRecoveryCode(string $code): void
    {
        $codes = array_map(
            fn (string $existing) => hash_equals($existing, $code) ? RecoveryCode::generate() : $existing,
            $this->recoveryCodes(),
        );

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        RecoveryCodeReplaced::dispatch($this, $code);
    }

    public function twoFactorQrCodeUrl(): string
    {
        return app(TwoFactorProvider::class)->qrCodeUrl(
            config('javaabu-auth.two_factor.issuer') ?: config('app.name'),
            $this->twoFactorLabel(),
            (string) $this->twoFactorSecret(),
        );
    }

    /**
     * The account name shown in the authenticator app
     */
    public function twoFactorLabel(): string
    {
        return (string) ($this->email ?? $this->getAuthIdentifier());
    }

    public function twoFactorQrCodeSvg(): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd,
            ),
        ))->writeString($this->twoFactorQrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }
}
