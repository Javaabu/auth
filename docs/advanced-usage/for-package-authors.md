---
title: For package authors
sidebar_position: 0.5
---

Packages that add account features (a mobile number page, notification preferences, …) or other ways to log in build on javaabu/auth instead of their own auth code. This page shows the pieces, with a mobile number package as the example.

# composer.json

```json
{
    "require": {
        "javaabu/auth": "^3.0"
    }
}
```

# An account page per user type

Let the app register the page for the user types that should have it, with a route macro, the way `Route::authRoutes()` works. Put the routes behind `auth.type:{type}` and give them the type's route name prefix, so the page belongs to that type:

```php
namespace Javaabu\MobileVerification;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Javaabu\Auth\UserTypes;

class MobileVerificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::macro('mobileNumberRoutes', function (string $type, array $options = []) {
            /** @var Router $this */
            $user_type = UserTypes::get($type);

            $this->middleware([...($options['middleware'] ?? ['web']), "auth.type:{$type}"])
                ->prefix($options['prefix'] ?? '')
                ->name($user_type->routeNamePrefix())
                ->group(function (Router $router) {
                    $router->get('settings/mobile-number', [MobileNumbersController::class, 'edit'])->name('mobile-number');
                    $router->put('settings/mobile-number', [MobileNumbersController::class, 'update'])->name('mobile-number.update');
                });
        });

        // Show the page in the account navigation of every type that has the route
        UserTypes::accountLink('mobile-number', 'Mobile number', position: 30);
    }
}
```

```php
// the app's routes/web.php
Route::authRoutes('user');
Route::mobileNumberRoutes('user');
```

# Account navigation links

The account navigation (the Blade partial `auth::partials.account-nav` and React's `AccountLayout`) shows Profile (position 10), Security (20), the links packages add, and Log out. A link shows for a user type only when the type has the link's route, so registering it for every type is safe:

```php
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;

// every type that has the `mobile-number` route (`mobile-number`, `admin.mobile-number`, …)
UserTypes::accountLink('mobile-number', 'Mobile number');

// one type, with another route name, a closure label and a position
UserTypes::get('admin')->accountLink('phone', fn (UserType $type) => __('Phone'), route: 'mobile-number', position: 15);

// apps remove links per type
UserTypes::get('admin')->withoutAccountLink('mobile-number');
```

| Argument | What |
| --- | --- |
| `key` | Unique; the same key replaces a link (`profile` and `security` are the built-in ones) |
| `label` | An English string, translated with `__()` when shown, or a closure given the type |
| `route` | The route name without the type's prefix; defaults to the key |
| `position` | Lower comes first; Profile is 10, Security 20, the default 100 |

Links are read when a page renders, so registering them in `boot()` is enough, in any order with the routes. Add the label's Dhivehi to the package's `lang/dv.json`.

Pages get the links as the `account_links` prop (`[{key, label, url, active}]`), the current page marked `active` (and `aria-current="page"`).

# Rendering the page like auth's own

Render with the type's stack and layouts through auth's `ViewRenderer`, so the page gets the same props as auth's pages (`auth_type`, `urls`, `account_links`, `status`, and `strings` in React) and extends the type's `account` layout:

```php
use Illuminate\Http\Request;
use Javaabu\Auth\UserTypes;
use Javaabu\Auth\Views\ViewRenderer;

class MobileNumbersController
{
    public function edit(Request $request, ViewRenderer $views)
    {
        $type = UserTypes::fromRequest($request);

        return $views->render($type, 'mobile-number', [
            'mobile' => $type->auth()->user()->mobile,
        ], 'account');
    }
}
```

`ViewRenderer` renders `auth::{page}` and `auth::{Page}`, so for a page of your own package, build the same data and render your own view with it:

```php
use Javaabu\Auth\Support\PageData;

$data = [...app(PageData::class)->shared($type), 'mobile' => $user->mobile];

return $type->viewStack() === 'inertia'
    ? Inertia::render('mobile-verification::MobileNumber', [...$data, 'strings' => $strings])
    : view('mobile-verification::mobile-number', [
        ...$data,
        'user_type' => $type,
        'layout' => $type->layoutName('account'),
        'layout_section' => config('javaabu-auth.layout_section', 'content'),
    ]);
```

```blade
{{-- resources/views/mobile-number.blade.php --}}
@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Mobile number')">…</x-forms::card>
@endsection
```

The package's account layout includes the navigation. In an app's own layout, `@include('auth::partials.account-nav')` works on any page of a user type; it finds the type and links itself.

In React, wrap the page in `AccountLayout` and pass the links:

```tsx
import { AccountLayout, type AuthPageProps } from "@javaabu/auth";

// `title` and `mobile` are this package's own props; `strings` is auth's (for "Account" and "Log out")
export default function MobileNumber({ urls, strings, account_links, title, mobile }: AuthPageProps & { title: string; mobile: string | null }) {
  return (
    <AccountLayout title={title} page="mobile-number" urls={urls} strings={strings} links={account_links}>
      …
    </AccountLayout>
  );
}
```

# Another way to log in

Implement `Javaabu\Auth\Contracts\LoginMethod` and finish with `StartAuthenticatedSession`; see [Alternative login methods](alternative-login-methods.md).

# Rules

- Never assume the `web` guard or `App\Models\User`. Take a user type name, resolve it with `UserTypes::get()`, and use `$type->auth()`, `$type->route()` and `$type->newModel()`.
- Protect pages with `auth.type:{type}`, and sensitive actions with `auth.password.confirm:{type}`.
- Name routes with the type's prefix (`$type->routeNamePrefix()`), so each type gets its own.
- Register account links with an English label and add its Dhivehi; use logical utilities only in views.
- Test with two user types on two guards.
