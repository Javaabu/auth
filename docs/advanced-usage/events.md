---
title: Events and activity log
sidebar_position: 5
---

# Laravel's events

The package fires Laravel's own events with the type's guard name: `Illuminate\Auth\Events\Login`, `Logout`, `Failed` (wrong password), `Lockout` (rate limited), `Registered`, `Verified`, `PasswordReset`, `PasswordResetLinkSent`.

# The package's events

All in `Javaabu\Auth\Events`, with a public `$user`:

| Event | When |
| --- | --- |
| `AccountLocked` | Too many failed logins locked the account |
| `PasswordUpdated` | The user changed their password |
| `ProfileUpdated` | The user updated their profile |
| `AccountDeleted` | The user deleted their account |
| `TwoFactorAuthenticationEnabled` | A secret was created (not yet confirmed) |
| `TwoFactorAuthenticationConfirmed` | The user confirmed with a code; two-factor is on |
| `TwoFactorAuthenticationDisabled` | Two-factor was turned off or reset |
| `TwoFactorAuthenticationChallenged` | A login is waiting for the code |
| `TwoFactorAuthenticationFailed` | A wrong code or recovery code |
| `RecoveryCodesGenerated` | New recovery codes |
| `RecoveryCodeReplaced` | A recovery code was used (also has `$code`) |

laravel/passkeys fires `PasskeyRegistered`, `PasskeyVerified` and `PasskeyDeleted`.

# Activity log

With [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) installed and `log_activity` on, these are logged with the user as causer and subject, and the guard and IP in the properties:

| Description | Event |
| --- | --- |
| `login` | `Login` |
| `logout` | `Logout` |
| `failed_login` | `Failed`, when the account exists |
| `lockout` | `AccountLocked` |
| `password_reset` | `PasswordReset` |
| `password_reset_link_sent` | `PasswordResetLinkSent` |
| `password_changed` | `PasswordUpdated` |
| `two_factor_enabled` | `TwoFactorAuthenticationConfirmed` |
| `two_factor_disabled` | `TwoFactorAuthenticationDisabled` |
| `passkey_added` | `PasskeyRegistered` |
| `passkey_removed` | `PasskeyDeleted` |

Without the package, nothing is logged and nothing breaks. `Javaabu\Auth\Support\ActivityLogger::isAvailable()` tells you which.
