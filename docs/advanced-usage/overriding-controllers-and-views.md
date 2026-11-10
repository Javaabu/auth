---
title: Overriding controllers and views
sidebar_position: 3
---

# Controllers

`Route::authRoutes()` takes its controllers from `javaabu-auth.controllers`. Extend the package's controller and point the key at yours:

```php
namespace App\Http\Controllers\Auth;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\Auth\Http\Controllers\AuthenticatedSessionController as BaseController;

class AuthenticatedSessionController extends BaseController
{
    public function create(Request $request): View|Responsable
    {
        return $this->render($request, 'login', [
            'can_reset_password' => true,
            'can_register' => false,
            'can_use_passkeys' => true,
            'announcement' => __('The portal is closed on Friday.'),
        ]);
    }
}
```

```php
'controllers' => [
    'login' => App\Http\Controllers\Auth\AuthenticatedSessionController::class,
    // …
],
```

Keys: `login`, `register`, `password_reset_link`, `new_password`, `email_verification_prompt`, `verify_email`, `email_verification_notification`, `confirm_password`, `account_status`, `required_password`, `two_factor_challenge`, `two_factor`, `passkey_login`, `passkey_confirmation`, `passkeys`, `profile`, `security`, `password`, `sessions`, `api_tokens`, `api_token_login`, `user_accounts`.

Inside a controller, `$this->userType($request)` is the route's type, `$this->user($request)` the signed-in user on its guard, and `$this->render($request, $page, $data, $layout)` renders with the type's view stack.

# Blade views

```bash
php artisan vendor:publish --tag=auth-views
```

Edit the copies in `resources/views/vendor/auth`. Keep logical utilities (`ms-`, `text-start`) so the pages still work right to left.

# React pages

```bash
php artisan manifest:override auth js/Pages/Login.tsx
```

The copy in `resources/vendor/auth/js/Pages/Login.tsx` (`manifest.overrides_path`) wins over the package's. Restart `npm run dev` after adding it. Import the package's pieces from `@javaabu/auth`.

# Per user type

A type can use another stack or layouts:

```php
'admin' => [
    'views' => 'blade',
    'layouts' => ['auth' => 'admin.layouts.guest', 'account' => 'admin.layouts.app'],
],
```
