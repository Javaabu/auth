---
title: Passkeys
sidebar_position: 10
---

Passkeys let users log in with their fingerprint, face or screen lock (WebAuthn), and confirm their password the same way. The package builds on [laravel/passkeys](https://github.com/laravel/passkeys-server), Laravel's own WebAuthn server, which Fortify and the starter kits use.

# Setup

1. Turn on the `passkeys` feature for the type: `'features' => ['passkeys' => true]`.
2. Give the model `Concerns\HasPasskeys` and implement `Laravel\Passkeys\Contracts\PasskeyUser` (the package's `User` does).
3. Run the migrations: the package's `passkeys` table has `user_type` and `user_id`, so every user type shares it. Don't also publish laravel/passkeys' own migration.
4. Passkeys need HTTPS (or `localhost`). laravel/passkeys reads its settings from `config/passkeys.php`:

```dotenv
APP_URL=https://example.mv
PASSKEYS_USER_HANDLE_SECRET=   # defaults to APP_KEY
```

```php
// config/passkeys.php (php artisan vendor:publish --tag=passkeys-config)
'relying_party_id' => 'example.mv',
'allowed_origins' => ['https://example.mv', 'https://admin.example.mv'],
```

The package registers the passkey routes per user type, so laravel/passkeys' own routes are turned off (`Passkeys::ignoreRoutes()`) and its `guard`, `middleware` and `redirect` settings aren't used.

# What users get

- **Login page:** "Log in with a passkey" (`passkey.login-options`, `passkey.login`). The browser offers the passkeys saved for the site; no username needed.
- **Security page:** the user's passkeys, with the authenticator's name (iCloud Keychain, Google Password Manager, …), when it was added and last used, a remove button, and "Add a passkey" with a name.
- **Confirm password page:** "Confirm with a passkey" when the user has one.

A passkey login skips the two-factor code. The account checks of `auth.type` (status, lockout, verified email) still apply after it. A passkey belongs to one user of one type: an admin's passkey can't log in on the public login page, and an admin and a user with the same id keep separate passkeys.

# Blocking logins

```php
use Laravel\Passkeys\Passkeys;
use Illuminate\Validation\ValidationException;

Passkeys::authorizeLoginUsing(function ($request, $user, $passkey) {
    if ($user->isBanned()) {
        throw ValidationException::withMessages(['credential' => [__('Your account has been banned.')]]);
    }

    return true;
});
```

# The browser side

The Blade pages load `auth::passkeys`, which wires up the buttons. In React, use `PasskeyButton`, or the functions it uses:

```tsx
import { PasskeyButton, registerPasskey, verifyPasskey } from "@javaabu/auth";

<PasskeyButton
  mode="verify"
  optionsUrl={urls["passkey.login-options"]}
  url={urls["passkey.login"]}
  label={strings.log_in_with_passkey}
  unsupported={strings.passkeys_unsupported}
  failed={strings.passkey_login_failed}
/>;

const result = await registerPasskey({ optionsUrl, url, data: { name: "ލެޕްޓޮޕް" } });
```

The endpoints speak the same JSON as `@laravel/passkeys`, so that client works too.

# Events

laravel/passkeys fires `PasskeyRegistered`, `PasskeyVerified` and `PasskeyDeleted` (`Laravel\Passkeys\Events`). With activitylog installed, adding and removing are logged.
