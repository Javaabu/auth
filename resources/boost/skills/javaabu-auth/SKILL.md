---
name: javaabu-auth
description: Authentication with javaabu/auth for Laravel apps with one or more kinds of users (user types such as public users and admins, each with its own guard, routes and pages). Use whenever the project depends on javaabu/auth and the task adds or changes a user type, login, registration, password reset, email verification, password confirmation, two-factor authentication, passkeys, API tokens (Sanctum or Passport), account status or lockout, the profile or security pages, protects routes for signed-in users, or overrides the package's controllers, actions or pages.
license: MIT
metadata:
  author: Javaabu
---

# Authentication with javaabu/auth

The package owns the auth controllers, routes and pages; the app configures **user types** and protects its own routes. The docs ship with the package: `vendor/javaabu/auth/llms.txt` lists every page in `vendor/javaabu/auth/docs/`.

## Adding a user type

1. A guard, provider and password broker in `config/auth.php`:

```php
'guards' => [
    'web_admin' => ['driver' => 'session', 'provider' => 'admins'],
],
'providers' => [
    'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
],
'passwords' => [
    'admins' => ['provider' => 'admins', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
],
```

2. The model and its table:

```php
namespace App\Models;

use Javaabu\Auth\Models\User as AuthUser;

class Admin extends AuthUser
{
    protected $table = 'admins';
}
```

```php
use Javaabu\Auth\Schema\UserSchema;

Schema::create('admins', function (Blueprint $table) {
    UserSchema::columns($table);
});
```

3. The type in `config/javaabu-auth.php`:

```php
'admin' => [
    'guard' => 'web_admin',
    'passwords' => 'admins',
    'home' => '/admin',
    'features' => ['registration' => false, 'passkeys' => true],
    'two_factor' => 'required',
],
```

4. Its routes, and the app's pages behind `auth.type`:

```php
Route::authRoutes('admin', ['prefix' => 'admin']);

Route::middleware('auth.type:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('home');
});
```

Route names get the type's `route_name` prefix (`admin.login`, `admin.password.request`, `admin.profile.edit`, `admin.security.edit`). The default `user` type has `route_name` `''`, so its names are `login`, `register`, … as Laravel expects.

## Rules

- Find a type with `UserTypes::get($name)`, `UserTypes::for($user)`, `UserTypes::forGuard($guard)` or `UserTypes::fromRequest($request)`; build URLs with `$type->route('login')` and `$type->homeUrl($user)`, never `route('login')` for a non-default type.
- `auth.type:{type}` checks the guard, account status, verified email, required password change and required two-factor setup. Use it instead of `auth:{guard}` on app pages. `auth.password.confirm:{type}` asks for the password again, per type.
- Change what registration or profile updates do by binding your own `Javaabu\Auth\Contracts\CreatesNewUsers` (or `ResetsUserPasswords`, `UpdatesUserPasswords`, `UpdatesUserProfileInformation`, `DeletesUsers`) in a service provider. Every method receives the `UserType`. Don't edit the package's controllers.
- Change a controller by extending the package's and setting it in `config('javaabu-auth.controllers')`. Change where users go by rebinding `Javaabu\Auth\Http\Responses\LoginResponse` (and `RegisterResponse`, `LogoutResponse`) or the type's `home`.
- New login methods (mobile number and code, …) implement `Javaabu\Auth\Contracts\LoginMethod` and finish with `Javaabu\Auth\Actions\StartAuthenticatedSession`, which runs the two-factor challenge. Don't call `Auth::login()` yourself.
- Two-factor authentication is per type: `'two_factor' => 'off' | 'optional' | 'required'`. Use the actions in `Javaabu\Auth\Actions\TwoFactor`, never write secrets directly. In tests get a code with `app(TwoFactorProvider::class)->currentCode($secret)`.
- Passkeys need the `passkeys` feature, `Concerns\HasPasskeys` + `Laravel\Passkeys\Contracts\PasskeyUser` on the model, and the package's `passkeys` table (with `user_type`). Don't publish laravel/passkeys' own migration or routes.
- API tokens: the model's `HasApiTokens` trait (Sanctum or Passport) picks the driver; create tokens with `Javaabu\Auth\Support\ApiTokens::create()`. Mobile token logins come from `Route::authApiRoutes('user')` (Sanctum).
- Admin actions on other users (unlock, reset two-factor, require a new password, log out everywhere) come from `Route::userAccountActions('user', ['middleware' => ['web', 'auth.type:admin']])`, behind the `auth.*` abilities and the `{action}_{type plural}` permissions. Run `php artisan permissions:sync`.
- A page in the account area (a package's or the app's) goes behind `auth.type:{type}`, uses the type's route name prefix, and is added to the account navigation with `UserTypes::accountLink('key', 'English label')` or `$type->accountLink(…)`; don't edit the navigation partial. See `docs/advanced-usage/for-package-authors.md`.
- Every user-facing string goes through `__()` with an English key; add the Dhivehi to `lang/dv.json`. In overridden views use logical utilities (`ms-`, `text-start`), `dir="ltr"` on email, password and code inputs, `dir="auto"` on names.

See [references/common-mistakes.md](references/common-mistakes.md) for what goes wrong, and [references/testing.md](references/testing.md) for tests.
