<?php

namespace Javaabu\Auth\Contracts;

/**
 * A user who can turn on TOTP two-factor authentication
 */
interface TwoFactorAuthenticatable
{
    public function hasEnabledTwoFactorAuthentication(): bool;

    public function twoFactorSecret(): ?string;

    /** @return array<int, string> */
    public function recoveryCodes(): array;

    public function replaceRecoveryCode(string $code): void;

    public function twoFactorQrCodeUrl(): string;

    public function twoFactorQrCodeSvg(): string;
}
