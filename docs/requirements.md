---
title: Requirements
sidebar_position: 1.1
---

- PHP 8.4 or 8.5
- Laravel 12 or 13

It installs:

- [javaabu/forms](https://github.com/Javaabu/forms) v2 for the Blade pages, and [javaabu/manifest](https://github.com/Javaabu/manifest) to ship the scripts and React pages
- [javaabu/permissions](https://github.com/Javaabu/permissions) v3 for the account-management permissions
- [laravel/passkeys](https://github.com/laravel/passkeys-server) for WebAuthn, [pragmarx/google2fa](https://github.com/antonioribeiro/google2fa) and [bacon/bacon-qr-code](https://github.com/Bacon/BaconQrCode) for two-factor authentication
- [mobiledetect/mobiledetectlib](https://github.com/serbanghita/Mobile-Detect) to name browsers on the browser sessions list

Optional, each detected when installed:

| Package | For |
| --- | --- |
| `inertiajs/inertia-laravel` ^2.0 or ^3.0 | The Inertia React pages (`javaabu-auth.views` = `inertia`) |
| `laravel/sanctum` ^4.0 | API tokens and SPA authentication |
| `laravel/passport` ^13.0 | OAuth2 API authentication |
| `spatie/laravel-activitylog` ^4.10 | Logging logins, failed logins, lockouts and password changes |

The React pages need `react` and `react-dom` 18.3 or 19, `@inertiajs/react` 2 or 3, `@javaabu/forms` and `@javaabu/onu` in the app. Blade apps need none of them, only Vite to build the pages' scripts: the package declares them for its pages only (javaabu/manifest's `for: 'pages'`), so `php artisan manifest:check` asks for them only when the app installs an Inertia adapter. It lists what's missing.

`laravel/passkeys` is still 0.x. The package pins `^0.2.1`; expect a minor update when it reaches 1.0.
