---
title: Two-factor authentication
sidebar_position: 9
---

TOTP codes from an authenticator app (Google Authenticator, 1Password, Microsoft Authenticator, …), with recovery codes. Each user type chooses:

```php
'two_factor' => 'off',      // users can't turn it on, and logins never ask for a code
'two_factor' => 'optional', // the default: users turn it on from the security page
'two_factor' => 'required', // users must set it up before they can use the app, and can't turn it off
```

The model needs `Concerns\TwoFactorAuthenticatable` and its contract, and the columns from `UserSchema::twoFactorColumns()`. The columns and their encryption are the same as laravel/fortify's, so users moving from a Fortify app keep their two-factor setup.

# Turning it on

On the security page (behind password confirmation):

1. **Turn on** (`two-factor.enable`) creates a secret and 8 recovery codes. The page shows the QR code and the setup key.
2. The user enters a code from their app (`two-factor.confirm`). Only then is it on (`two_factor_confirmed_at`), and the page shows the recovery codes once.
3. **Regenerate recovery codes** (`two-factor.regenerate-recovery-codes`) replaces them and shows the new ones once.
4. **Turn off** (`two-factor.disable`) removes it, unless the type requires it.

For your own UI, the JSON endpoints `two-factor.qr-code` (`{svg, url}`), `two-factor.secret-key` and `two-factor.recovery-codes` return the pieces.

# Logging in

After the right password, a user with two-factor authentication on goes to `two-factor-challenge` instead of being logged in. They enter a code, or one of their recovery codes (which is then replaced). Each code is accepted once; 5 wrong codes in a minute are refused for the rest of the minute. A challenge started on one type's login page can't be finished on another's.

Passkey logins skip the challenge: a passkey is already two factors.

# Required

With `'two_factor' => 'required'`, `auth.type:{type}` sends users who haven't set it up to the security page, and only lets them use the security page, the two-factor routes, password confirmation and logout until they have.

# Settings

```php
'two_factor' => [
    'issuer' => null,        // shown in the app; defaults to config('app.name')
    'window' => 1,           // 30 second periods either side of now that are accepted
    'secret_length' => 16,
    'recovery_codes' => 8,
],
```

The account name in the app is the user's email; override `twoFactorLabel()` on the model to change it.

# In code

```php
use Javaabu\Auth\Actions\TwoFactor\ConfirmTwoFactorAuthentication;
use Javaabu\Auth\Actions\TwoFactor\DisableTwoFactorAuthentication;
use Javaabu\Auth\Actions\TwoFactor\EnableTwoFactorAuthentication;
use Javaabu\Auth\Actions\TwoFactor\GenerateNewRecoveryCodes;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;

app(EnableTwoFactorAuthentication::class)($user);
app(ConfirmTwoFactorAuthentication::class)($user, '123456');
app(DisableTwoFactorAuthentication::class)($user);
app(GenerateNewRecoveryCodes::class)($user);

$user->hasEnabledTwoFactorAuthentication();
$user->hasPendingTwoFactorAuthentication();
$user->recoveryCodes();
$user->twoFactorQrCodeSvg();

app(TwoFactorProvider::class)->verify($user->twoFactorSecret(), $code);
app(TwoFactorProvider::class)->currentCode($secret); // in tests
```

Events: `TwoFactorAuthenticationEnabled`, `TwoFactorAuthenticationConfirmed`, `TwoFactorAuthenticationDisabled`, `TwoFactorAuthenticationChallenged`, `TwoFactorAuthenticationFailed`, `RecoveryCodesGenerated`, `RecoveryCodeReplaced`, all in `Javaabu\Auth\Events` with a `$user`.

Admins with the permission can reset a user's two-factor authentication: see [Managing accounts](managing-accounts.md).
