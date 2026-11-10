---
title: Configuration
sidebar_position: 4
---

`config/javaabu-auth.php` (publish with `--tag=auth-config`). Every option has a default; one user type on the `web` guard needs none of it.

| Key | Default | What |
| --- | --- | --- |
| `user_types` | one `user` type on `web` | The user types; see [User types](../basic-usage/user-types.md) |
| `views` | `AUTH_VIEWS` or `blade` | `blade` or `inertia` |
| `layouts.auth` | `auth::layouts.auth` | Blade layout of the guest pages |
| `layouts.account` | `auth::layouts.account` | Blade layout of the profile and security pages |
| `layout_section` | `content` | The section the pages fill |
| `max_login_attempts` | `AUTH_MAX_LOGIN_ATTEMPTS` or 5 | Failed logins in a row before an account locks; `null` never locks |
| `rate_limits.login` | 5 | Login attempts per minute per type, username and IP |
| `rate_limits.two_factor` | 5 | Codes per minute per user |
| `rate_limits.passkeys` | 10 | Passkey requests per minute |
| `rate_limits.password_reset` | 6 | Reset link and reset requests per minute per IP |
| `rate_limits.verification` | 6 | Verification requests per minute per IP |
| `rate_limits.api_tokens` | 6 | Token requests per minute per IP |
| `password_timeout` | `AUTH_PASSWORD_TIMEOUT` or 10800 | Seconds a password confirmation lasts |
| `two_factor.issuer` | `app.name` | Name shown in authenticator apps |
| `two_factor.window` | 1 | 30 second periods either side of now that are accepted |
| `two_factor.secret_length` | 16 | Length of new secrets |
| `two_factor.recovery_codes` | 8 | How many recovery codes users get |
| `api` | `AUTH_API` or `none` | `none`, `sanctum`, `passport` or `both` |
| `api_tokens.abilities` | `['*']` | Default Sanctum abilities or Passport scopes of personal access tokens |
| `api_tokens.expiration` | `null` | Minutes before new Sanctum tokens expire |
| `register_permissions` | `true` | Register the account-management permissions with javaabu/permissions |
| `managed_user_types` | `[]` (all) | The user types those permissions are registered for |
| `policy` | `Policies\UserAccountPolicy` | The policy behind the `auth.*` account abilities |
| `log_activity` | `true` | Log auth events with spatie/laravel-activitylog when installed |
| `controllers.*` | the package's | The controllers of the routes; see [Overriding controllers and views](overriding-controllers-and-views.md) |

Passkey settings (relying party, origins, timeout) are laravel/passkeys' own, in `config/passkeys.php`. Guards, providers and password brokers are Laravel's, in `config/auth.php`.

The v2 package merged its options into `config/auth.php`; v3 keeps them out of it. See [Upgrading](../upgrading.md).
