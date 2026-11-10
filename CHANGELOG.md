# Changelog

All notable changes will be documented in this file. See [docs/upgrading.md](docs/upgrading.md) for moving from v2.

## 3.0.0 - unreleased

- User types: any number of kinds of users, each with its own model, guard, password broker, route names, home, view stack, layouts, features, two-factor mode, lockout limit and login methods (`config('javaabu-auth.user_types')`, `UserTypes::register()`)
- `Route::authRoutes()` registers a type's login, logout, registration, password reset, email verification, password confirmation, two-factor challenge, passkey, account status, required password change, profile and security routes, with Laravel starter kit route names
- Controllers for every page, overridable from `javaabu-auth.controllers`; actions behind contracts (`CreatesNewUsers`, `ResetsUserPasswords`, `UpdatesUserPasswords`, `UpdatesUserProfileInformation`, `DeletesUsers`); rebindable responses
- Middleware: `auth.type`, `auth.guest`, `auth.active`, `auth.verified`, `auth.password-updated`, `auth.two-factor`, `auth.password.confirm` (kept per user type), `auth.user-type`
- Rate limiting, timeboxed password checks, account lockout after failed logins, per-type guest redirects
- TOTP two-factor authentication with recovery codes, off, optional or required per type, in laravel/fortify's column format
- Passkeys with laravel/passkeys for every user type: login, password confirmation and management, in one morph table
- API authentication with Sanctum, Passport or both: `php artisan auth:install`, `Route::authApiRoutes()` (Sanctum token logins with two-factor), personal access tokens on the security page
- Profile page (name, email, account deletion) and security page (password, two-factor, passkeys, browser sessions, API tokens)
- `Route::userAccountActions()`: unlock, reset two-factor, require a new password and log out everywhere, behind `UserAccountPolicy` and permissions registered with javaabu/permissions
- `LoginMethod` contract and `StartAuthenticatedSession` for alternative login methods (javaabu/mobile-verification)
- Account navigation links for packages and apps: `UserTypes::accountLink()`, `UserType::accountLink()` / `withoutAccountLink()`, the `account_links` page prop, and `AccountLayout`'s `links`
- Pages in Blade (javaabu/forms v2) and Inertia React (@javaabu/forms, shipped through javaabu/manifest), with a shared WebAuthn client and components exported as `@javaabu/auth`
- The React pages' npm packages (`@javaabu/forms`, `@javaabu/onu`, React, `@inertiajs/react`) are declared for the pages only, so `manifest:check` doesn't ask Blade apps for them
- Dhivehi translations; right-to-left pages
- Optional activity logging with spatie/laravel-activitylog
- Docs for people and agents, `llms.txt`, Laravel Boost guideline and skill; `docs/` ships in the Composer package
- Requires PHP 8.4, Laravel 12 or 13
- Breaking: rewritten on top of user types instead of abstract laravel/ui controllers; config moves from `config/auth.php` to `config/javaabu-auth.php`; `Javaabu\Auth\User` is now `Javaabu\Auth\Models\User` with traits; no longer requires laravel/ui, javaabu/helpers, javaabu/activitylog, javaabu/passport, javaabu/schema or spatie/laravel-medialibrary; the new-email verification flow is removed
