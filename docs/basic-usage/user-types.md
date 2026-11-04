---
title: User types
sidebar_position: 1
---

A user type is one kind of user: public users, admins, staff, vendors. Each has its own model, guard, password broker, routes, pages and features. Everything in the package (controllers, middleware, emails, permissions) works out the type from the route or the model, so nothing assumes the `web` guard or `App\Models\User`.

# In config

```php
// config/javaabu-auth.php
use App\Models\Admin;

'user_types' => [
    'user' => [
        'guard' => 'web',
        'passwords' => 'users',
        'route_name' => '',
        'home' => 'dashboard',
        'features' => ['passkeys' => true, 'delete_account' => true],
    ],

    'admin' => [
        'model' => Admin::class,
        'guard' => 'web_admin',
        'passwords' => 'admins',
        'home' => '/admin',
        'features' => ['registration' => false, 'passkeys' => true],
        'two_factor' => 'required',
        'max_login_attempts' => 5,
        'layouts' => ['account' => 'admin.layouts.app'],
    ],
],
```

The first type is the default, used where no route or model says otherwise.

| Option | Default | What |
| --- | --- | --- |
| `model` | the guard's eloquent provider model | The user model |
| `guard` | the type's name | A session guard from `config/auth.php` |
| `passwords` | the plural of the name (`admins`) | The password broker |
| `route_name` | the type's name | Prefix of the route names: `admin` → `admin.login`. `''` gives `login`, which Laravel's defaults expect |
| `home` | `/` | Where users go after logging in: a path, a route name, or a closure given the user |
| `views` | `javaabu-auth.views` | `blade` or `inertia` |
| `layouts` | `javaabu-auth.layouts` | `['auth' => …, 'account' => …]` Blade layouts |
| `username` | `email` | The column users log in with |
| `features` | see below | Features to turn on or off |
| `two_factor` | `optional` | `off`, `optional` or `required` |
| `max_login_attempts` | `javaabu-auth.max_login_attempts` | Lock the account after this many failed logins in a row; `null` never locks |
| `login_methods` | `[]` | [Alternative login methods](../advanced-usage/alternative-login-methods.md) |

# Features

| Feature | Default | What |
| --- | --- | --- |
| `registration` | on | The register page |
| `password_reset` | on | Forgot and reset password |
| `email_verification` | on | Verification emails, the notice page, and `auth.type` sending unverified users to it (for models that implement `MustVerifyEmail`) |
| `password_confirmation` | on | The confirm password page; the security page and its actions ask for the password first |
| `profile` | on | The profile page |
| `delete_account` | off | Users delete their own account from the profile page |
| `sessions` | on | The browser sessions list (needs the `multi_auth_database` session driver) |
| `passkeys` | off | Passkey login, confirmation and management |
| `api_tokens` | off | Personal access tokens on the security page (needs Sanctum or Passport) |

```php
'features' => ['registration' => false, 'passkeys' => true],
'features' => ['passkeys', 'api_tokens'], // a list turns them on
```

# In code

`UserTypes::register()` adds a type, or changes one from config. Call it in a service provider's `register()` or `boot()`, before the routes load:

```php
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;

UserTypes::register('vendor', fn (UserType $type) => $type
    ->model(Vendor::class)
    ->guard('web_vendor')
    ->passwords('vendors')
    ->home(fn (Vendor $vendor) => route('vendors.dashboard', $vendor))
    ->disable(UserType::REGISTRATION)
    ->enable(UserType::PASSKEYS)
    ->twoFactor('required'));

UserTypes::get('user')->enable(UserType::API_TOKENS);
```

# Finding a type

```php
UserTypes::get('admin');            // by name
UserTypes::for($admin);             // by model or model class
UserTypes::forGuard('web_admin');   // by guard
UserTypes::fromRequest($request);   // the type of the current route
UserTypes::default();               // the first type
UserTypes::all();

$admin->userType();                 // with the HasUserType trait

$type = UserTypes::get('admin');
$type->route('login');              // url of admin.login
$type->routeNameFor('login');       // 'admin.login'
$type->homeUrl($admin);
$type->auth();                      // Auth::guard('web_admin')
$type->broker();                    // Password::broker('admins')
$type->hasFeature(UserType::PASSKEYS);
$type->requiresTwoFactor();
```
