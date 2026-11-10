# auth: notes for AI coding agents

`javaabu/auth` v3 is multi-auth for Laravel: user types (each with a guard, password broker, routes, pages and features), every auth flow, two-factor authentication, passkeys, API tokens and admin account actions. Other Javaabu packages (javaabu/mobile-verification, the skeletons) build on `UserTypes`, `Route::authRoutes()`, `LoginMethod` and `StartAuthenticatedSession`, so treat those as public contracts.

## Layout

| Path | What |
| --- | --- |
| `src/UserType.php`, `src/UserTypeRegistry.php`, `src/UserTypes.php` | A user type, the registry (config + code), the facade |
| `src/Routing` | `AuthRoutes` (`Route::authRoutes()`), `AuthApiRoutes` (`Route::authApiRoutes()`), `UserAccountRoutes` (`Route::userAccountActions()`) |
| `src/Http/Controllers` | One controller per page or flow; resolved from `config('javaabu-auth.controllers')` |
| `src/Http/Middleware` | `auth.type`, `auth.guest`, `auth.active`, `auth.verified`, `auth.password-updated`, `auth.two-factor`, `auth.password.confirm`, `auth.user-type` |
| `src/Http/Responses` | Login, logout, register and 2FA challenge responses (rebindable) |
| `src/Actions` | Login pipeline (`AttemptLogin` → `StartAuthenticatedSession` → `LoginUser`), user actions behind `src/Contracts`, `TwoFactor/*`, `IssueApiToken` |
| `src/Concerns`, `src/Contracts`, `src/Models/User.php` | Model traits, their contracts, the base model |
| `src/TwoFactor` | TOTP provider (google2fa, each code once) and recovery codes |
| `src/Passkeys` | Morph `Passkey` model and the laravel/passkeys action subclasses |
| `src/Sessions` | `multi_auth_database` session handler, `Session` model, `Agent` |
| `src/Support` | `PageData` (shared props), `Strings` (React strings), `ApiTokens`, `AccountState`, `ActivityLogger` |
| `src/Policies/UserAccountPolicy.php` | The `auth.*` account abilities |
| `src/Views/ViewRenderer.php` | Blade (`auth::login`) or Inertia (`auth::Login`) per user type |
| `resources/views` | Blade pages and layouts (javaabu/forms v2 components) |
| `resources/js` | Inertia React pages (`Pages`), components and `lib` (exported as `@javaabu/auth`), and the Blade scripts (`passkeys.ts`, `auth.ts`), registered with javaabu/manifest |
| `lang/en.json`, `lang/dv.json` | English keys and Dhivehi |
| `docs`, `llms.txt`, `resources/boost` | Docusaurus pages, the index for AI assistants, the Laravel Boost guideline and skill |
| `tests` | Testbench tests with two types: `user` (`web`, `User`, no route prefix) and `admin` (`web_admin`, `Admin`, `admin.` names under `/admin`). `tests/Support/SoftwareAuthenticator.php` makes real passkey credentials. Tests needing Inertia, Sanctum or Passport are in the `optional` group. `tests/js` are Vitest tests |

## Rules

- Follow Javaabu codestyle ([boost-javaabu-guidelines](https://github.com/Javaabu/boost-javaabu-guidelines)): snake_case variables and properties, camelCase methods, early returns, `#[Test]` and `it_…` test names. Pint enforces formatting.
- Nothing may assume the `web` guard, `App\Models\User` or global route names. Resolve the type from the route (`UserTypes::fromRequest()`) or the model, and test behaviour on both test types, including that one type can't use another's login, challenge, confirmation, passkeys or tokens.
- Security-sensitive changes (login, 2FA, passkeys, tokens, password reset) need tests of the failure cases too.
- Inertia, Sanctum, Passport and activitylog are optional: feature-detect them (`class_exists`, `function_exists`), never `use` their classes in code that always runs, and keep CI's "Without the optional packages" job green.
- npm packages for the React pages and `@javaabu/auth` go in the provider's `npm(..., for: 'pages')` call; the Blade scripts (`auth.ts`, `passkeys.ts`, `lib/webauthn.ts`) import none, and a test keeps it that way. If one ever must, declare that package in the default (assets) scope.
- Every page exists in Blade and React with the same props. Use logical utilities only; a test rejects `ml-`, `text-left` and the like.
- Every user-facing string goes through `__()` with an English key and gets a Dhivehi line in `lang/dv.json` and an English line in `lang/en.json` (a test checks). React strings go in `Support\Strings`; regenerate `tests/js/strings.dv.json` after changing them:

  ```bash
  php -r 'require "vendor/autoload.php"; $dv = json_decode(file_get_contents("lang/dv.json"), true); $out = []; foreach (Javaabu\Auth\Support\Strings::ALL as $k => $en) { $out[$k] = $dv[$en] ?? $en; } file_put_contents("tests/js/strings.dv.json", json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");'
  ```

- List uncertain Dhivehi in `docs/advanced-usage/translations.md`. Use Thaana in tests of user-facing behaviour.
- Every change needs tests, docs (linked from `llms.txt`), the Boost skill if it changes how the package is used, and a line in `CHANGELOG.md`.
- Work on the `next-gen` branch; don't commit to `main`.

## Commands

```bash
composer install
composer lint      # vendor/bin/pint --test
composer format    # vendor/bin/pint
composer test      # vendor/bin/phpunit
pnpm install       # the React pages' dev tooling
pnpm check         # typecheck and Vitest
```
