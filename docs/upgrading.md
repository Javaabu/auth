---
title: Upgrading from v2
sidebar_position: 1.25
---

v3 is a rewrite. v2 gave you abstract controllers built on laravel/ui's traits, to extend once per user type; v3 gives you the controllers, routes and pages, configured per user type. Plan to replace your auth controllers, routes and views rather than adapt them.

# Requirements

- PHP 8.4+ and Laravel 12+ (v2: PHP 8.2+, Laravel 11+).
- No longer required: `laravel/ui`, `javaabu/helpers`, `javaabu/activitylog`, `javaabu/passport`, `javaabu/schema`, `spatie/laravel-medialibrary`. Require them in your app if you still use them.
- New: `javaabu/forms` v2, `javaabu/manifest`, `javaabu/permissions` v3, `laravel/passkeys`, `pragmarx/google2fa`, `bacon/bacon-qr-code`.

# Config

v2 merged its config into `config/auth.php` (`max_login_attempts`, `passport_guards`, and a default `web_admin` guard). v3 has `config/javaabu-auth.php` and leaves `config/auth.php` to you.

1. Keep your guards, providers and brokers in `config/auth.php`. v2's defaults gave `web_admin` (session) and `api_admin`/`api` (passport); check they're still defined.
2. Remove `max_login_attempts` and `passport_guards` from `config/auth.php`. `max_login_attempts` moves to `javaabu-auth.max_login_attempts`.
3. Run `php artisan auth:install` and describe your user types in `user_types`. For each of your v2 user models, the `guardName()`, `homeUrl()` and password reset/verification route names become `guard`, `home` and `route_name`.

# Models

`Javaabu\Auth\User` is now `Javaabu\Auth\Models\User`, and is much smaller:

| v2 | v3 |
| --- | --- |
| `UserContract` methods `guardName()`, `homeUrl()`, `loginUrl()`, `getRouteForPasswordReset()`, `getRouteForEmailVerification()`, `emailVerificationRedirectUrl()`, `inactiveNoticeUrl()`, `passwordUpdateUrl()` | The user type: `$user->userType()->guardName()`, `->homeUrl($user)`, `->route('login')`. Remove them from your models |
| `HasStatus`, `status` cast | `Concerns\HasAccountStatus`; `UserStatuses` keeps its values (`approved`, `pending`, `banned`); `getMessage()` is now `message()`, `getColor()` is `color()` |
| `$user->is_active`, `is_locked_out`, `is_approved`, `is_banned`, `is_pending`, `status_message` attributes | `isActive()`, `isLockedOut()`, `isApproved()`, `isBanned()`, `isPending()`, `accountStatusMessage()` |
| `scopeActive()` | `approved()->notLockedOut()->whereNotNull('email_verified_at')` |
| `incrementLoginAttempts()` / `resetLoginAttempts()` | Same names; they now save |
| `PasswordUpdatable`, `requiresPasswordUpdate()` | `Concerns\RequiresPasswordUpdate` |
| `sessions()`, `deleteOtherSessionRecords()` | `Concerns\HasSessions` |
| `DetectsGmailAliases` (`Traits`) | `Concerns\DetectsGmailAliases` |
| `HasApiTokens` (Passport), `HasUserIdentifier`, `findForPassport()` | Add Sanctum's or Passport's `HasApiTokens` yourself; see [API authentication](basic-usage/api-authentication.md) |
| `IsAdminModel`, `LogsActivity`, `CausesActivity`, media library avatar, `initials`, `list_name`, `userVisible` scope | Removed: add them back in your model if you use them |
| `setPasswordAttribute()` hashing | The `hashed` cast |
| `new_email` flow (`requestEmailUpdate()`, `updateEmail()`) | Removed: changing the email on the profile page marks it unverified and sends a verification link to the new address |

The table columns are the same, plus two-factor columns. Add them:

```php
Schema::table('users', function (Blueprint $table) {
    UserSchema::twoFactorColumns($table);
});
```

`status` was a native enum column (javaabu/schema); v3's `UserSchema` makes it a string. Existing columns keep working.

# Controllers and routes

Delete the controllers that extended `Javaabu\Auth\Http\Controllers\Auth\*` and their routes, and register the package's:

```php
// before
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
// … one set per user type

// after
Route::authRoutes('user');
Route::authRoutes('admin', ['prefix' => 'admin']);
```

Route names follow Laravel's starter kits now. With `route_name` `admin`: `admin.login`, `admin.password.request`, `admin.password.reset`, `admin.verification.notice`, `admin.password.confirm`. v2's `password.new-password` is `password.required`.

To change a page's behaviour, extend the package controller and set it in `javaabu-auth.controllers`. See [Overriding controllers and views](advanced-usage/overriding-controllers-and-views.md).

# Middleware

| v2 | v3 |
| --- | --- |
| `auth:web_admin`, `active:web_admin`, `password-update-not-required:web_admin` | `auth.type:admin` (all of them, plus verified email and required 2FA) |
| `active` | `auth.active` |
| `email.verified` | `auth.verified` |
| `password-update-not-required` | `auth.password-updated` |
| `inactive`, `needs-verification`, `password-update-required`, `inactive-email.unverified` | Built into the package's routes |
| `password.confirm` | `auth.password.confirm:{type}`, kept per user type |

The parameter is the user type's name, not the guard.

# Views

v2 asked you for every view (`getLoginForm()`, …). v3 ships them: Blade with javaabu/forms v2 components, or Inertia React. Point `javaabu-auth.layouts` at your layouts, or publish the views and move your markup into them.

# Events and logging

v2's listeners logged to activitylog through javaabu/activitylog and counted failed logins. v3 does the same with spatie/laravel-activitylog when installed (see [Events and activity log](advanced-usage/events.md)) and counts failures in the login action. `Javaabu\Auth\Events\PasswordChanged` is now `PasswordUpdated`; `EmailUpdated` is removed.

# Sessions

The `multi_auth_database` session driver is unchanged. `Javaabu\Auth\Session\Session` is now `Javaabu\Auth\Sessions\Session`, and `Javaabu\Auth\Agent` is `Javaabu\Auth\Sessions\Agent`.

# Translations

v2's `auth::validation.unique-gmail` key is now the JSON string "The same gmail :attribute is already taken.". All strings are JSON (`lang/dv.json`).
