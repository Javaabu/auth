---
title: Introduction
sidebar_position: 1.0
---

# Auth

[javaabu/auth](https://github.com/Javaabu/auth) is authentication for Laravel apps with more than one kind of user. Admins and public users (or staff, vendors, …) each get their own guard, password broker, routes, pages and rules, from one package:

- **User types.** Each type has a model, a guard, a password broker, a home page and a set of features. Admins can have registration off and two-factor authentication required while public users sign up freely.
- **Every auth page.** Login, registration, forgot and reset password, email verification, password confirmation, the two-factor challenge, passkeys, and profile and security pages (password, two-factor, passkeys, browser sessions, API tokens).
- **Two-factor authentication.** TOTP with recovery codes, off, optional or required per user type, in the same columns as laravel/fortify.
- **Passkeys.** Log in and confirm the password with WebAuthn passkeys, built on Laravel's own `laravel/passkeys`.
- **API authentication.** Sanctum, Passport or both, with token endpoints per user type and `php artisan auth:install`.
- **Blade and Inertia React.** Every page in javaabu/forms Blade components and in React with @javaabu/forms, chosen by a config option. Dhivehi and right to left throughout.
- **Overridable.** Controllers from config, actions from the container, routes from a macro, views publishable, and admin actions behind a policy and javaabu/permissions permissions.

```php
// config/javaabu-auth.php
'user_types' => [
    'user' => [
        'guard' => 'web',
        'route_name' => '',
        'home' => '/dashboard',
    ],
    'admin' => [
        'guard' => 'web_admin',
        'passwords' => 'admins',
        'home' => '/admin',
        'features' => ['registration' => false, 'passkeys' => true],
        'two_factor' => 'required',
    ],
],
```

```php
// routes/web.php
Route::authRoutes('user');
Route::authRoutes('admin', ['prefix' => 'admin']);

Route::middleware('auth.type:admin')->prefix('admin')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('admin.home');
});
```

Start with [Installation & Setup](installation-and-setup.md). Coming from v2? Read [Upgrading](upgrading.md).
