# Common mistakes with javaabu/auth

## Assuming one guard

```php
// Wrong: the admin is on web_admin
$admin = auth()->user();
return redirect()->route('login');

// Right
$type = UserTypes::get('admin');
$admin = $type->auth()->user();
return redirect()->to($type->route('login'));
```

Inside a route registered by `Route::authRoutes()` or behind `auth.type:admin`, `UserTypes::fromRequest($request)` gives the type, and `auth.type` sets the default guard for the request so `$request->user()` works.

## Protecting pages with `auth:` only

`auth:web_admin` lets pending, banned, locked and unverified admins through. Use `auth.type:admin`.

## Writing auth controllers

The package has every auth page. To change behaviour, bind an action or extend a controller (see the skill). To add fields to registration, bind a `CreatesNewUsers` action and override the `register` view or page.

## Logging users in by hand

`Auth::guard('web')->login($user)` skips the two-factor challenge and the session regeneration. Use `StartAuthenticatedSession`:

```php
return app(StartAuthenticatedSession::class)($request, UserTypes::get('user'), $user, remember: true);
```

## Registering a type after the routes

`UserTypes::register()` and `->loginMethod()` must run before `Route::authRoutes()`; register types in a service provider, not in a route file after the macro.

## Publishing laravel/passkeys' migration

Its `passkeys` table has no `user_type`, so passkeys of different user types collide. The package ships its own migration; use only that.

## Expecting `lang/vendor/auth/dv.json` to work

Laravel doesn't read JSON translations from `lang/vendor`. Put changed lines in the app's `lang/dv.json`.

## Physical direction utilities

`ml-4`, `text-left` break the pages in Dhivehi. Use `ms-4`, `text-start`.
