---
title: Password reset
sidebar_position: 6
---

With the `password_reset` feature, the login page links to `password.request`.

1. The user enters their email on `forgot-password`. The type's password broker emails a link to `reset-password/{token}?email=…` (the type's own `password.reset` route). The page answers the same whether or not the account exists.
2. On the reset page they choose a new password. The `ResetsUserPasswords` action validates it (`Password::default()`, confirmed) and saves it; the remember token is replaced, Laravel's `PasswordReset` event fires, a locked account is unlocked and a required password change is cleared.
3. They're sent to the login page with a status message.

Each type uses its own broker (`passwords`), so a token from the users' broker doesn't work on the admins' reset page.

The email is Laravel's, with the link changed to the type's route: `Javaabu\Auth\Notifications\ResetPassword`. `ResetPassword::createUrlUsing()` and `toMailUsing()` still work. Its strings are translated in `lang/dv.json`.

Set password rules for the whole app with Laravel's `Password::defaults()`:

```php
use Illuminate\Validation\Rules\Password;

Password::defaults(fn () => Password::min(10)->mixedCase()->uncompromised());
```
