---
title: Installation & Setup
sidebar_position: 1.2
---

# Install

```bash
composer require javaabu/auth
php artisan auth:install
php artisan migrate
```

`auth:install` publishes `config/javaabu-auth.php` and the passkeys migration, and asks which API authentication you want (see [API authentication](basic-usage/api-authentication.md)). Pass `--api=none|sanctum|passport|both` to skip the question.

Then run `php artisan permissions:sync` so the [account-management permissions](basic-usage/managing-accounts.md) exist.

# The user model

Extend the package's model, which has every feature:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\Models\User as AuthUser;

class User extends AuthUser
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];
}
```

Or add only the traits you want to your own model; see [The user model](basic-usage/user-models.md).

The table needs the package's columns. `UserSchema::columns()` adds them all:

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Auth\Schema\UserSchema;

Schema::create('users', function (Blueprint $table) {
    UserSchema::columns($table);
});
```

For an existing table, add what you need:

```php
Schema::table('users', function (Blueprint $table) {
    UserSchema::accountColumns($table);   // status, login_attempts, require_password_update, last_login_at
    UserSchema::twoFactorColumns($table); // two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at
});
```

# One user type

The default config has one type, `user`, on the `web` guard and the `users` password broker, with route names without a prefix (`login`, `password.request`, …). Register its routes:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::authRoutes();

Route::middleware('auth.type:user')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
```

Set where users go after logging in:

```php
// config/javaabu-auth.php
'user_types' => [
    'user' => [
        'guard' => 'web',
        'passwords' => 'users',
        'route_name' => '',
        'home' => 'dashboard', // a route name, a path or a closure
    ],
],
```

That's a working app: `/login`, `/register`, `/forgot-password`, `/settings/profile`, `/settings/security`, and the rest. See [Routes](basic-usage/routes.md) for the full list.

# Several user types

Add a guard, a provider and a password broker for each type in `config/auth.php`:

```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'web_admin' => ['driver' => 'session', 'provider' => 'admins'],
],

'providers' => [
    'users' => ['driver' => 'eloquent', 'model' => App\Models\User::class],
    'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
],

'passwords' => [
    'users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    'admins' => ['provider' => 'admins', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
],
```

Then the types, and their routes. See [User types](basic-usage/user-types.md).

# Pages

The pages render with Blade by default. They extend `auth::layouts.auth` (guest pages) and `auth::layouts.account` (profile and security), small layouts that load the javaabu/forms assets. Point them at your own layouts:

```php
'layouts' => [
    'auth' => 'layouts.guest',
    'account' => 'layouts.app',
],
'layout_section' => 'content',
```

For Inertia React, set `'views' => 'inertia'` and follow [Pages](basic-usage/pages.md).

# Publishing

```bash
php artisan vendor:publish --tag=auth-config
php artisan vendor:publish --tag=auth-views
php artisan vendor:publish --tag=auth-translations
php artisan vendor:publish --tag=auth-migrations
```

After publishing the migrations, stop the package from loading its own: `AuthServiceProvider::ignoreMigrations()` in a service provider's `register()`.

# The session driver

To list a user's browsers on the security page and log them out of the others, use the `multi_auth_database` session driver, which stores the user type with each session:

```dotenv
SESSION_DRIVER=multi_auth_database
```

```php
Schema::create('sessions', function (Blueprint $table) {
    UserSchema::sessionColumns($table);
});
```
