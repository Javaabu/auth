---
title: Routes
sidebar_position: 2
---

Nothing is registered automatically. `Route::authRoutes()` registers one user type's routes where you call it, so you choose the prefix, the middleware and the domain:

```php
// routes/web.php
Route::authRoutes('user');
Route::authRoutes('admin', [
    'prefix' => 'admin',
    'middleware' => ['web'],
    'domain' => null,
    'account_prefix' => 'settings',
    'except' => ['passkeys'],
]);
```

| Option | Default | What |
| --- | --- | --- |
| `prefix` | `''` | URL prefix of every route |
| `middleware` | `['web']` | Middleware of every route |
| `domain` | none | Domain of every route |
| `account_prefix` | `settings` | Prefix of the profile and security pages |
| `except` | `[]` | Features to leave out even when the type has them: `registration`, `password_reset`, `email_verification`, `password_confirmation`, `passkeys`, `two_factor`, `profile`, `delete_account`, `security`, `sessions`, `api_tokens`, `login_method:{key}` |

# The routes

Names are prefixed with the type's `route_name` (`admin.login`). Routes only exist for the type's features.

| Method | URL | Name | |
| --- | --- | --- | --- |
| GET, POST | `login` | `login`, `login.store` | Log in |
| POST | `logout` | `logout` | Log out of this type only |
| GET, POST | `register` | `register`, `register.store` | `registration` |
| GET, POST | `forgot-password` | `password.request`, `password.email` | `password_reset` |
| GET | `reset-password/{token}` | `password.reset` | `password_reset` |
| POST | `reset-password` | `password.update` | `password_reset` |
| GET, POST | `two-factor-challenge` | `two-factor.login`, `two-factor.login.store` | two-factor not `off` |
| GET, POST | `passkeys/login/options`, `passkeys/login` | `passkey.login-options`, `passkey.login` | `passkeys` |
| GET | `verify-email` | `verification.notice` | `email_verification` |
| GET | `verify-email/{id}/{hash}` | `verification.verify` | `email_verification`, signed |
| POST | `email/verification-notification` | `verification.send` | `email_verification` |
| GET, POST | `confirm-password` | `password.confirm`, `password.confirm.store` | `password_confirmation` |
| GET, POST | `passkeys/confirm/options`, `passkeys/confirm` | `passkey.confirm-options`, `passkey.confirm` | `passkeys` and `password_confirmation` |
| GET | `account/inactive` | `account.inactive` | Why a pending, banned or locked account can't continue |
| GET, PUT | `update-password` | `password.required`, `password.required.update` | Choosing a new password when made to |
| GET, PATCH | `settings/profile` | `profile.edit`, `profile.update` | `profile` |
| DELETE | `settings/profile` | `profile.destroy` | `delete_account` |
| GET | `settings/security` | `security.edit` | Password confirmation first |
| PUT | `settings/password` | `user-password.update` | |
| POST, DELETE | `settings/two-factor` | `two-factor.enable`, `two-factor.disable` | two-factor not `off` |
| POST | `settings/two-factor/confirm` | `two-factor.confirm` | |
| GET | `settings/two-factor/qr-code`, `…/secret-key`, `…/recovery-codes` | `two-factor.qr-code`, `two-factor.secret-key`, `two-factor.recovery-codes` | JSON |
| POST | `settings/two-factor/recovery-codes` | `two-factor.regenerate-recovery-codes` | |
| GET, POST | `settings/passkeys/options`, `settings/passkeys` | `passkey.registration-options`, `passkey.store` | `passkeys` |
| DELETE | `settings/passkeys/{passkey_id}` | `passkey.destroy` | `passkeys` |
| DELETE | `settings/sessions` | `sessions.destroy` | `sessions` |
| POST, DELETE | `settings/api-tokens`, `settings/api-tokens/{token_id}` | `api-tokens.store`, `api-tokens.destroy` | `api_tokens` |

Guest routes have `auth.guest:{type}`, which sends signed-in users of that type to its home. Signed-in routes have `auth.type:{type}`. The security page and the two-factor, passkey and session actions are behind `auth.password.confirm:{type}`.

# Your own pages

Protect the app's pages with `auth.type`:

```php
Route::middleware('auth.type:admin')->prefix('admin')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('admin.home');
});
```

See [Middleware](middleware.md). Laravel's `auth:web_admin` works too: guests on it are sent to `admin.login`, not `login`.

# Other route macros

- `Route::authApiRoutes('user', ['prefix' => 'api'])`: Sanctum token endpoints, see [API authentication](api-authentication.md).
- `Route::userAccountActions('user', ['prefix' => 'admin/users', 'middleware' => ['web', 'auth.type:admin']])`: what admins do to other accounts, see [Managing accounts](managing-accounts.md).
