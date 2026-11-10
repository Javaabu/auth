---
title: Pages (Blade and React)
sidebar_position: 13
---

Every page ships twice: Blade with [javaabu/forms](https://github.com/Javaabu/forms) components, and Inertia React with [@javaabu/forms](https://github.com/Javaabu/react-forms) and [@javaabu/onu](https://www.npmjs.com/package/@javaabu/onu). Both get the same data and behave the same.

```php
'views' => env('AUTH_VIEWS', 'blade'), // or 'inertia'; a user type can choose its own with 'views'
```

| Page | Blade view | Inertia page |
| --- | --- | --- |
| Log in | `auth::login` | `auth::Login` |
| Register | `auth::register` | `auth::Register` |
| Forgot password | `auth::forgot-password` | `auth::ForgotPassword` |
| Reset password | `auth::reset-password` | `auth::ResetPassword` |
| Verify email | `auth::verify-email` | `auth::VerifyEmail` |
| Confirm password | `auth::confirm-password` | `auth::ConfirmPassword` |
| Two-factor challenge | `auth::two-factor-challenge` | `auth::TwoFactorChallenge` |
| Account not active | `auth::account-inactive` | `auth::AccountInactive` |
| Required new password | `auth::update-required-password` | `auth::UpdateRequiredPassword` |
| Profile | `auth::settings.profile` | `auth::Settings/Profile` |
| Security | `auth::settings.security` | `auth::Settings/Security` |

# Props

Every page gets:

| Prop | What |
| --- | --- |
| `auth_type` | `{name, features, two_factor, username, login_methods}` |
| `urls` | The type's route URLs by name without the prefix: `urls['login.store']`, `urls['profile.edit']`, `urls.home` |
| `account_links` | The account navigation: `[{key, label, url, active}]` for Profile, Security and links packages added (see [For package authors](../advanced-usage/for-package-authors.md#account-navigation-links)) |
| `status` | The flashed status message, if any |
| `strings` | React only: the translated strings, keyed as in `Javaabu\Auth\Support\Strings` |

and its own: `can_reset_password`, `can_register` and `can_use_passkeys` (login), `token` and `email` (reset), `email` (verify), `message` (inactive), `profile`, `must_verify_email` and `can_delete_account` (profile), `two_factor`, `passkeys`, `sessions`, `api_tokens` and `plain_text_token` (security; a section is `null` when the type doesn't have it). The TypeScript types are in `@javaabu/auth` (`LoginPageProps`, `SecurityPageProps`, …).

# Blade

The guest pages extend `javaabu-auth.layouts.auth`, the profile and security pages `javaabu-auth.layouts.account`, filling `javaabu-auth.layout_section`. A user type can set its own `layouts`. The pages load their scripts through javaabu/manifest (`auth::passkeys`, `auth::auth`), so the layout needs `@manifestStyles` and `@manifestScripts`, or manifest's auto-injection.

```blade
{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @manifestStyles
</head>
<body>
    @yield('content')
    @manifestScripts
</body>
</html>
```

Override a view with `php artisan vendor:publish --tag=auth-views`, then edit `resources/views/vendor/auth/…`.

# Inertia React

Install the Inertia adapter and the npm packages. Blade-only apps skip these; `manifest:check` checks them once `@inertiajs/react` is in `package.json` (or with `--scope=pages`):

```bash
composer require inertiajs/inertia-laravel
npm install @javaabu/forms@github:Javaabu/react-forms#next-gen @javaabu/onu @inertiajs/react react react-dom
php artisan manifest:check
```

Resolve package pages with javaabu/manifest, and submit forms as Inertia visits:

```tsx
// resources/js/app.tsx
import { createInertiaApp } from "@inertiajs/react";
import { FormsProvider } from "@javaabu/forms";
import { inertia } from "@javaabu/forms/inertia";
import { resolvePage } from "@javaabu/manifest/inertia";
import { createRoot } from "react-dom/client";

createInertiaApp({
  resolve: (name) => resolvePage(name, import.meta.glob("./pages/**/*.tsx")),
  setup({ el, App, props }) {
    createRoot(el).render(
      <FormsProvider router={inertia}>
        <App {...props} />
      </FormsProvider>,
    );
  },
});
```

The pages render their content only; wrap them in your layout with Inertia's persistent layouts, for example in `resolve`:

```tsx
resolve: async (name) => {
  const page = await resolvePage<any>(name, import.meta.glob("./pages/**/*.tsx"));

  if (name.startsWith("auth::Settings/")) {
    page.default.layout ??= (children) => <AppLayout>{children}</AppLayout>;
  }

  return page;
},
```

Override a page with `php artisan manifest:override auth js/Pages/Login.tsx`. `AccountLayout` takes the `account_links` prop as `links`. The components the pages use are exported from `@javaabu/auth` for building your own: `AuthCard`, `AccountLayout`, `StatusAlert`, `PasskeyButton`, `TwoFactorSettings`, `PasskeysSettings`, `SessionsSettings`, `ApiTokensSettings`, `t()`, and the WebAuthn functions.

# Forms and stencil

The auth forms have five fields at most, so they're written directly with the form components rather than defined with javaabu/stencil.

# Dhivehi and right to left

Every string is translated (`lang/dv.json`), the pages use logical utilities only (`ms-`, `text-start`), email, password and code inputs are `dir="ltr"`, and name inputs are `dir="auto"`. See [Dhivehi and translations](../advanced-usage/translations.md).
