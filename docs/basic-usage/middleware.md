---
title: Middleware
sidebar_position: 3
---

| Alias | Class | What |
| --- | --- | --- |
| `auth.type:{type}` | `Http\Middleware\Authenticate` | Signed in on the type's guard, then every check below. `auth.type:{type},basic` only checks the guard |
| `auth.guest:{type}` | `Http\Middleware\RedirectIfAuthenticated` | Sends signed-in users of the type to its home |
| `auth.active:{type}` | `Http\Middleware\EnsureAccountIsActive` | Pending, banned and locked accounts go to `account.inactive` (403 for JSON) |
| `auth.verified:{type}` | `Http\Middleware\EnsureEmailIsVerified` | Unverified emails go to `verification.notice`, when the type has `email_verification` |
| `auth.password-updated:{type}` | `Http\Middleware\EnsurePasswordIsUpdated` | Users made to change their password go to `password.required` |
| `auth.two-factor:{type}` | `Http\Middleware\EnsureTwoFactorIsEnabled` | For types with `two_factor` `required`: users without it go to the security page to set it up |
| `auth.password.confirm:{type}` | `Http\Middleware\RequirePassword` | Asks for the password again, every `javaabu-auth.password_timeout` seconds. An optional second parameter sets the timeout |
| `auth.user-type:{type}` | `Http\Middleware\SetUserType` | Only tells the package which type the route belongs to |

`auth.type:admin` is all you usually need on app routes:

```php
Route::middleware('auth.type:admin')->group(function () {
    // signed in as an admin, active, verified, password changed if required, 2FA set up if required
});

Route::middleware(['auth.type:admin', 'auth.password.confirm:admin'])->group(function () {
    Route::delete('/admin/everything', DestroyEverythingController::class);
});
```

Password confirmation is kept per user type: confirming as a public user doesn't unlock admin pages in the same browser.
