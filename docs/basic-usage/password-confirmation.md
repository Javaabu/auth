---
title: Password confirmation
sidebar_position: 8
---

`auth.password.confirm:{type}` asks signed-in users for their password before sensitive pages, then lets them through for `javaabu-auth.password_timeout` seconds (3 hours by default, `AUTH_PASSWORD_TIMEOUT`).

The package puts it on the security page and on turning two-factor authentication on or off, managing passkeys and logging out other sessions. Use it on your own routes:

```php
Route::middleware(['auth.type:admin', 'auth.password.confirm:admin'])
    ->delete('/admin/users/{user}', [UsersController::class, 'destroy']);

Route::middleware('auth.password.confirm:admin,600') // ten minutes
    ->get('/admin/billing', BillingController::class);
```

Users with a passkey can confirm with it instead (`passkey.confirm`). JSON requests get `423` until confirmed.

Each user type keeps its own confirmation in the session (`auth.password_confirmed_at.{type}`), unlike Laravel's `password.confirm`, so an admin who is also signed in as a public user confirms each separately.

```php
use Javaabu\Auth\Http\Middleware\RequirePassword;

RequirePassword::isConfirmed($request, UserTypes::get('admin'));
RequirePassword::markConfirmed($request, UserTypes::get('admin'));
```
