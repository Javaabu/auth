---
title: Logging in and registering
sidebar_position: 5
---

# Logging in

`POST login` takes the type's username (`email` by default), `password` and `remember`. It:

1. Refuses when the username and IP address made `rate_limits.login` (5) failed attempts in the last minute (`429`, the `Lockout` event).
2. Refuses a locked account (see below).
3. Checks the password against the type's user provider, within a fixed time so the answer doesn't reveal whether the account exists.
4. Fires Laravel's `Failed` event and counts the failure on the account when the password is wrong.
5. Asks for a two-factor code when the user has two-factor authentication on (see [Two-factor authentication](two-factor-authentication.md)).
6. Logs in on the type's guard, regenerates the session, clears failed attempts and records `last_login_at`.
7. Redirects to the intended URL or the type's home. JSON requests get `{"two_factor": false, "redirect": "…"}`.

A user of one type can't log in on another type's login page, even with the same email and password.

# Logging out

`POST logout` logs out of that type's guard only. When another type is still signed in on the same browser (a public user who is also an admin), the session stays and only its id is regenerated.

# Locking accounts

With `max_login_attempts` set (5 by default, `AUTH_MAX_LOGIN_ATTEMPTS`) and the `LocksAfterFailedLogins` trait, the account locks after that many failed logins in a row, whatever the IP address. A locked account can't log in until the password is reset, or an admin unlocks it (see [Managing accounts](managing-accounts.md)). The `AccountLocked` event fires when it locks.

```php
'max_login_attempts' => null, // per type: never lock
```

# Registering

`POST register` takes `name`, the username, `password` and `password_confirmation`. It validates and creates the user with the `CreatesNewUsers` action, fires Laravel's `Registered` event (which sends the verification email for `MustVerifyEmail` models) and logs the user in. Emails are lowercased and must be unique among the type's users.

Change the fields and rules by binding your own action; see [Actions](../advanced-usage/actions.md).

# Rate limits

| Limiter | Default per minute | Key |
| --- | --- | --- |
| login (in `AttemptLogin`) | 5 | type, username and IP |
| two-factor (in the challenge) | 5 | type and user |
| `auth.passkeys` | 10 | credential or session, and IP |
| `auth.password-reset` | 6 | IP |
| `auth.verification` | 6 | IP |
| `auth.api-tokens` | 6 | IP |

Change the numbers in `javaabu-auth.rate_limits`, or redefine a named limiter in your app:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('auth.password-reset', fn ($request) => Limit::perHour(10)->by($request->ip()));
```
