---
title: API authentication
sidebar_position: 12
---

Choose Sanctum, Passport or both:

```bash
composer require laravel/sanctum   # and/or laravel/passport
php artisan auth:install --api=sanctum
```

`auth:install` sets `AUTH_API` in `.env` (`javaabu-auth.api`: `none`, `sanctum`, `passport` or `both`), publishes the driver's migrations (and makes Passport's keys), and prints the guard setup for each user type. Each user model uses one driver: the `HasApiTokens` trait it has decides which.

# Sanctum

```php
use Javaabu\Auth\Models\User as AuthUser;
use Laravel\Sanctum\HasApiTokens;

class User extends AuthUser
{
    use HasApiTokens;
}
```

## Tokens for mobile apps

`Route::authApiRoutes()` adds a login endpoint per user type that returns a token:

```php
// routes/api.php (already has the `api` middleware and prefix)
Route::authApiRoutes('user', ['prefix' => '', 'middleware' => []]);
Route::authApiRoutes('admin', ['prefix' => 'admin', 'middleware' => []]);

Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());
```

```http
POST /api/tokens
Accept: application/json

{"email": "ahmed@example.com", "password": "…", "device_name": "ޢަލީގެ ފޯނު"}
```

```json
{"token": "1|1aB…", "token_type": "Bearer"}
```

It checks the password, the account (pending, banned and locked accounts get a validation error) and, when the user has two-factor authentication on, a `code` or `recovery_code` in the same request. `DELETE /api/tokens/current` with the token revokes it. Names: `api.tokens.store`, `api.tokens.destroy` (`admin.api.tokens.store`, …).

| Option | Default | |
| --- | --- | --- |
| `prefix` | `api` | |
| `middleware` | `['api']` | |
| `auth_middleware` | `['auth:sanctum']` | For the revoke route |

## SPA authentication

Sanctum's cookie authentication works with the package's session logins. List every user type's session guard in `config/sanctum.php`: `'guard' => ['web', 'web_admin']`.

# Passport

```php
use Javaabu\Auth\Models\User as AuthUser;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class Admin extends AuthUser implements OAuthenticatable
{
    use HasApiTokens;
}
```

Passport 13 handles several user providers itself. Add a passport guard and a personal access client per type:

```php
// config/auth.php
'guards' => [
    'api' => ['driver' => 'passport', 'provider' => 'users'],
    'api_admin' => ['driver' => 'passport', 'provider' => 'admins'],
],
```

```bash
php artisan passport:client --personal --provider=users
php artisan passport:client --personal --provider=admins
```

A user's token only passes the guard of its own provider. OAuth flows (authorization code with PKCE, client credentials, device code) go through Passport's own `/oauth/*` routes. `Route::authApiRoutes()` is for Sanctum; with Passport use the OAuth flows or personal access tokens.

# Personal access tokens on the security page

Turn on the `api_tokens` feature for the type. Users create a named token (shown once) and delete their tokens on the security page, with whichever driver their model uses. In code:

```php
use Javaabu\Auth\Support\ApiTokens;

$plain_text = ApiTokens::create($user, 'CI', ['read']); // Sanctum abilities or Passport scopes
ApiTokens::list($user);
ApiTokens::revoke($user, $token_id);
ApiTokens::driverFor($user);   // 'sanctum', 'passport' or null
```

```php
'api_tokens' => [
    'abilities' => ['*'],  // default abilities (Sanctum) or scopes (Passport)
    'expiration' => null,  // minutes, Sanctum only (Passport uses its own expiry)
],
```
