<?php

namespace Javaabu\Auth\TwoFactor;

use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP secrets and codes, with each code accepted only once
 */
class TwoFactorProvider
{
    public function __construct(
        protected Google2FA $engine,
        protected ?Repository $cache = null,
    ) {}

    public function generateSecretKey(?int $length = null): string
    {
        return $this->engine->generateSecretKey($length ?? (int) config('javaabu-auth.two_factor.secret_length', 16));
    }

    public function qrCodeUrl(string $company_name, string $company_email, string $secret): string
    {
        return $this->engine->getQRCodeUrl($company_name, $company_email, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $window = (int) config('javaabu-auth.two_factor.window', 1);
        $cache_key = 'javaabu-auth.2fa_codes.' . md5($secret . $code);

        if ($this->cache?->get($cache_key)) {
            return false;
        }

        if (! $this->engine->verifyKey($secret, $code, $window)) {
            return false;
        }

        $this->cache?->put($cache_key, true, ($window + 1) * 60);

        return true;
    }

    /** The code for now, for tests and seeders */
    public function currentCode(string $secret): string
    {
        return $this->engine->getCurrentOtp($secret);
    }
}
