{{--
    javaabu/auth guidelines for AI code assistants

    Maintained by: Javaabu (https://javaabu.com)
--}}

## javaabu/auth

- This project authenticates its users with `javaabu/auth`. Each kind of user (public users, admins, …) is a **user type** in `config('javaabu-auth.user_types')` with its own guard, password broker, routes, pages and features. Never hard-code the `web` guard, `App\Models\User` or `route('login')`; use `Javaabu\Auth\UserTypes` (`UserTypes::get('admin')->route('login')`).
- Routes come from `Route::authRoutes('admin', ['prefix' => 'admin'])`; protect app pages with the `auth.type:admin` middleware. Don't write login, registration, password reset, verification, two-factor or passkey controllers by hand.
- Always activate the `javaabu-auth` skill when adding a user type, changing login or registration, protecting routes, working with two-factor authentication, passkeys, API tokens, account status or lockout, or overriding the package's pages, controllers or actions.
- Pages render as Blade (javaabu/forms) or Inertia React (`auth::Login`, … through javaabu/manifest), set by `config('javaabu-auth.views')`. Override them with `vendor:publish --tag=auth-views` or `php artisan manifest:override auth <file>`.
