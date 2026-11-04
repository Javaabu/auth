<?php

use Javaabu\Auth\Http\Controllers;

return [

    /*
    |--------------------------------------------------------------------------
    | User types
    |--------------------------------------------------------------------------
    |
    | Each kind of user (admins, public users, …) with its own guard, password
    | broker, routes and features. Register each type's routes with
    | Route::authRoutes('admin'). The first type is the default.
    |
    | Options (all optional):
    |   model               the user model; defaults to the guard's eloquent provider model
    |   guard               defaults to the type's name
    |   passwords           the password broker; defaults to the plural of the name
    |   route_name          prefix of the route names (`admin` → `admin.login`); '' for `login`
    |   home                path, route name or closure: where users go after logging in
    |   views               `blade` or `inertia`; defaults to javaabu-auth.views
    |   layouts             ['auth' => …, 'account' => …] Blade layouts; default to javaabu-auth.layouts
    |   username            the column users log in with
    |   features            turn features on or off, see Javaabu\Auth\UserType::DEFAULT_FEATURES
    |   two_factor          `off`, `optional` or `required`
    |   max_login_attempts  lock the account after this many failed logins in a row
    |   login_methods       classes implementing Javaabu\Auth\Contracts\LoginMethod
    |
    */

    'user_types' => [
        'user' => [
            'guard' => 'web',
            'passwords' => 'users',
            'route_name' => '',
            'home' => '/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Which stack the pages render with: `blade` (javaabu/forms) or `inertia`
    | (@javaabu/forms React pages shipped through javaabu/manifest). A user
    | type can choose its own. Requests that expect JSON always get JSON.
    |
    */

    'views' => env('AUTH_VIEWS', 'blade'),

    /*
    | The Blade layouts the pages extend, and the section they fill. `auth` is
    | for the guest pages (login, register, …), `account` for the signed-in
    | pages (profile, security). The package's own layouts are minimal pages
    | that load the javaabu/forms assets; point these at your app's layouts.
    */

    'layouts' => [
        'auth' => 'auth::layouts.auth',
        'account' => 'auth::layouts.account',
    ],

    'layout_section' => 'content',

    /*
    |--------------------------------------------------------------------------
    | Account lockout
    |--------------------------------------------------------------------------
    |
    | Lock accounts after this many failed logins in a row, until the user
    | resets their password. Needs the LocksAfterFailedLogins trait on the
    | model. Null never locks. A user type can set its own.
    |
    */

    'max_login_attempts' => env('AUTH_MAX_LOGIN_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    |
    | Attempts per minute. Each is a named rate limiter (`auth.login`, …) you
    | can redefine with RateLimiter::for() in your app.
    |
    */

    'rate_limits' => [
        'login' => 5,
        'two_factor' => 5,
        'passkeys' => 10,
        'password_reset' => 6,
        'verification' => 6,
        'api_tokens' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Password confirmation
    |--------------------------------------------------------------------------
    |
    | Seconds before users are asked for their password again on pages behind
    | the `auth.password.confirm` middleware. Each user type keeps its own
    | confirmation.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Two-factor authentication
    |--------------------------------------------------------------------------
    |
    | TOTP settings. `issuer` is shown in authenticator apps (default: app.name).
    | `window` is how many 30 second periods either side of now are accepted.
    |
    */

    'two_factor' => [
        'issuer' => null,
        'window' => 1,
        'secret_length' => 16,
        'recovery_codes' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | API authentication
    |--------------------------------------------------------------------------
    |
    | `none`, `sanctum`, `passport` or `both`. Run `php artisan auth:install`
    | to set it up. Turn on the `api_tokens` feature for the user types that
    | may create personal access tokens.
    |
    */

    'api' => env('AUTH_API', 'none'),

    'api_tokens' => [
        'abilities' => ['*'],
        'expiration' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Register this package's permissions (for admins managing other users'
    | accounts) through javaabu/permissions, for these user types' models.
    | Leave empty for every user type.
    |
    */

    'register_permissions' => true,

    'managed_user_types' => [],

    /*
    |--------------------------------------------------------------------------
    | Activity log
    |--------------------------------------------------------------------------
    |
    | Log logins, logouts, failed logins, lockouts and password changes when
    | spatie/laravel-activitylog is installed.
    |
    */

    'log_activity' => true,

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | The controllers Route::authRoutes() uses. Extend the package's controller
    | to change its behaviour.
    |
    */

    'controllers' => [
        'login' => Controllers\AuthenticatedSessionController::class,
        'register' => Controllers\RegisteredUserController::class,
        'password_reset_link' => Controllers\PasswordResetLinkController::class,
        'new_password' => Controllers\NewPasswordController::class,
        'email_verification_prompt' => Controllers\EmailVerificationPromptController::class,
        'verify_email' => Controllers\VerifyEmailController::class,
        'email_verification_notification' => Controllers\EmailVerificationNotificationController::class,
        'confirm_password' => Controllers\ConfirmablePasswordController::class,
        'account_status' => Controllers\AccountStatusController::class,
        'required_password' => Controllers\RequiredPasswordController::class,
        'two_factor_challenge' => Controllers\TwoFactorAuthenticatedSessionController::class,
        'two_factor' => Controllers\TwoFactorAuthenticationController::class,
        'passkey_login' => Controllers\PasskeyLoginController::class,
        'passkey_confirmation' => Controllers\PasskeyConfirmationController::class,
        'passkeys' => Controllers\PasskeysController::class,
        'profile' => Controllers\ProfileController::class,
        'security' => Controllers\SecurityController::class,
        'password' => Controllers\PasswordController::class,
        'sessions' => Controllers\BrowserSessionsController::class,
        'api_tokens' => Controllers\ApiTokensController::class,
        'user_accounts' => Controllers\UserAccountsController::class,
    ],

];
