---
title: API reference
sidebar_position: 10
---

All classes are in `Javaabu\Auth`.

# UserTypes (facade over UserTypeRegistry)

| Method | Returns |
| --- | --- |
| `register(string $name, array\|Closure\|null $config = null)` | `UserType`: adds a type or changes one |
| `get(string $name)` | `UserType`, or throws |
| `has(string $name)` | `bool` |
| `all()` | `array<string, UserType>` |
| `default()` | The first type |
| `for(Model\|string $user)` | The type of a model or model class (exact class first), or `null` |
| `forGuard(?string $guard)` | The type on a guard, or `null` |
| `fromRequest(?Request $request = null)` | The type of the current route, or `null` |
| `accountLink(string $key, string\|Closure $label, ?string $route = null, int $position = 100)` | `Support\AccountLink`: a link in every type's account navigation, shown where the route exists |
| `accountLinks()` | The links added for every type |
| `forget(string $name)`, `flush()` | For tests |

`UserTypeRegistry::ROUTE_ACTION` (`auth_user_type`) is the route action key holding the type.

# UserType

Setters return the type: `accountLink($key, $label, $route = null, $position = 100)`, `withoutAccountLink($key)`, `model()`, `guard()`, `passwords()`, `routeName()`, `home()`, `views()`, `layouts()`, `username()`, `features()`, `enable(...$features)`, `disable(...$features)`, `twoFactor()`, `maxLoginAttempts()`, `loginMethods()`, `loginMethod()`. `UserType::fromArray($name, $config)` builds one from config.

| Getter | Returns |
| --- | --- |
| `name()` | `string` |
| `modelClass()`, `newModel()` | The model class, a new instance |
| `guardName()`, `auth()` | The guard name, the `StatefulGuard` |
| `passwordBrokerName()`, `broker()` | The broker name, the `PasswordBroker` |
| `routeNamePrefix()`, `routeNameFor($route)`, `hasRoute($route)`, `route($route, $parameters = [], $absolute = true)` | Route names and URLs of the type |
| `homeUrl(?Model $user = null)` | Where users go after logging in |
| `viewStack()`, `layoutName($kind = 'auth')` | `blade`/`inertia`, a layout |
| `usernameColumn()` | `email` by default |
| `hasFeature($feature)`, `allFeatures()` | Features (`UserType::REGISTRATION`, `PASSWORD_RESET`, `EMAIL_VERIFICATION`, `PASSWORD_CONFIRMATION`, `PROFILE`, `DELETE_ACCOUNT`, `SESSIONS`, `PASSKEYS`, `API_TOKENS`) |
| `twoFactorMode()`, `hasTwoFactor()`, `requiresTwoFactor()` | `Enums\TwoFactorModes` (`OFF`, `OPTIONAL`, `REQUIRED`) |
| `maxLoginAttemptsAllowed()` | `?int` |
| `loginMethodsList()` | `array<string, LoginMethod>` |
| `accountLinks()` | `array<int, Support\AccountLink>`: the type's account navigation, built-ins included, whose routes exist, by position |
| `findByUsername(?string $username)` | The user, matched as typed then lowercased |
| `owns(mixed $user)` | Whether a model is one of this type's users |

# Route macros

- `Route::authRoutes(?string $type = null, array $options = [])`: `Routing\AuthRoutes`
- `Route::authApiRoutes(?string $type = null, array $options = [])`: `Routing\AuthApiRoutes`
- `Route::userAccountActions(string $type, array $options = [])`: `Routing\UserAccountRoutes`

# Models and traits

- `Models\User`: abstract model with every trait
- `Concerns\HasUserType`: `userType()`, `sendPasswordResetNotification()`, `sendEmailVerificationNotification()`
- `Concerns\HasAccountStatus`: `hasActiveAccount()`, `accountStatusMessage()`, `approve()`, `markAsPending()`, `ban()`, `isApproved()`, `isPending()`, `isBanned()`, scopes `approved`, `pending`, `banned`
- `Concerns\LocksAfterFailedLogins`: `maxLoginAttempts()`, `isLockedOut()`, `incrementLoginAttempts()`, `resetLoginAttempts()`, scopes `lockedOut`, `notLockedOut`
- `Concerns\RequiresPasswordUpdate`: `requiresPasswordUpdate()`, `requirePasswordUpdate()`, `clearRequirePasswordUpdate()`
- `Concerns\TwoFactorAuthenticatable`: `hasEnabledTwoFactorAuthentication()`, `hasPendingTwoFactorAuthentication()`, `twoFactorSecret()`, `recoveryCodes()`, `replaceRecoveryCode()`, `twoFactorQrCodeUrl()`, `twoFactorQrCodeSvg()`, `twoFactorLabel()`
- `Concerns\HasPasskeys`: `passkeys()`, `hasPasskeysEnabled()`, and laravel/passkeys' `getPasskeyUserHandle()`, `getPasskeyDisplayName()`, `getPasskeyUsername()`
- `Concerns\HasSessions`: `sessions()`, `deleteOtherSessionRecords()`
- `Concerns\DetectsGmailAliases`: scope `hasGmailAlias($email, $field = 'email')`
- `Passkeys\Passkey`: the passkey model (`user()` morph, `toDisplayArray()`)
- `Sessions\Session`: a session row (`user()`, `agent()`, `isCurrentDevice()`, `toDisplayArray()`)
- `Enums\UserStatuses`: `APPROVED`, `PENDING`, `BANNED`, with `label()`, `message()`, `color()`, `labels()`
- `Schema\UserSchema`: `columns()`, `accountColumns()`, `twoFactorColumns()`, `sessionColumns()`

# Contracts

`Contracts\LoginMethod`, `HasAccountStatus`, `LocksAfterFailedLogins`, `RequiresPasswordUpdate`, `TwoFactorAuthenticatable`, `CreatesNewUsers`, `ResetsUserPasswords`, `UpdatesUserPasswords`, `UpdatesUserProfileInformation`, `DeletesUsers`.

# Actions

`Actions\AttemptLogin`, `StartAuthenticatedSession` (`__invoke(Request, UserType, Authenticatable, bool $remember = false, bool $skip_two_factor = false)`, `needsTwoFactorChallenge()`), `LoginUser`, `IssueApiToken`, `CreateNewUser`, `ResetUserPassword`, `UpdateUserPassword`, `UpdateUserProfileInformation`, `DeleteUser`, `TwoFactor\EnableTwoFactorAuthentication`, `TwoFactor\ConfirmTwoFactorAuthentication`, `TwoFactor\DisableTwoFactorAuthentication`, `TwoFactor\GenerateNewRecoveryCodes`.

# Support

- `Support\ApiTokens`: `enabledDrivers()`, `isEnabled()`, `driverFor()`, `supports()`, `create()`, `list()`, `revoke()`
- `Support\AccountState`: `inactiveMessage($user)`, `isActive($user)`
- `Support\PageData`: `shared($type)`, `urls($type)`, `accountLinks($type)`, `strings()`
- `Support\AccountLink`: `key`, `label`, `route`, `position`, `label($type)`, `isAvailableFor($type)`, `toArray($type)`
- `Support\Strings::ALL`: the React pages' string keys and English text
- `Support\ActivityLogger`: `isAvailable()`, `EVENTS`
- `TwoFactor\TwoFactorProvider`: `generateSecretKey()`, `qrCodeUrl()`, `verify($secret, $code)` (each code once), `currentCode($secret)`
- `Views\ViewRenderer`: `render($type, $page, $data = [], $layout = 'auth')`, `inertiaPage($page)`
- `Http\Middleware\RequirePassword`: `sessionKey($type)`, `markConfirmed($request, $type)`, `isConfirmed($request, $type, $timeout = null)`
- `Policies\UserAccountPolicy`: `unlock()`, `resetTwoFactor()`, `requirePasswordUpdate()`, `logOut()`, `resource($type)`, `permission($action, $type)`, `ABILITIES`, `DESCRIPTIONS`
- `Rules\UniqueGmail`
- `Notifications\ResetPassword`, `Notifications\VerifyEmail`
- `AuthServiceProvider::ignoreMigrations()`, `MIDDLEWARE`, `ACTIONS`

# Commands

`php artisan auth:install {--api=none|sanctum|passport|both} {--force}`
