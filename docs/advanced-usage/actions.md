---
title: Actions
sidebar_position: 2
---

What happens to the user's data lives in actions behind contracts, as in Laravel's starter kits. Bind your own class to change it:

| Contract | Default | Used by |
| --- | --- | --- |
| `Contracts\CreatesNewUsers` | `Actions\CreateNewUser` | Registration |
| `Contracts\ResetsUserPasswords` | `Actions\ResetUserPassword` | Reset password |
| `Contracts\UpdatesUserPasswords` | `Actions\UpdateUserPassword` | Security page, required password change |
| `Contracts\UpdatesUserProfileInformation` | `Actions\UpdateUserProfileInformation` | Profile page |
| `Contracts\DeletesUsers` | `Actions\DeleteUser` | Deleting the account |

Every method gets the `UserType`, so one action can treat types differently.

```php
namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Javaabu\Auth\Actions\PasswordValidationRules;
use Javaabu\Auth\Contracts\CreatesNewUsers;
use Javaabu\Auth\Rules\UniqueGmail;
use Javaabu\Auth\UserType;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(UserType $type, array $input): Model
    {
        $user = $type->newModel();

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique($user::class), new UniqueGmail($user::class)],
            'mobile' => ['required', 'string', 'max:20'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'name' => $input['name'],
            'email' => mb_strtolower($input['email']),
            'mobile' => $input['mobile'],
            'password' => Hash::make($input['password']),
        ]);

        $user->approve();
        $user->save();

        return $user;
    }
}
```

```php
// AppServiceProvider::register()
$this->app->bind(\Javaabu\Auth\Contracts\CreatesNewUsers::class, \App\Actions\CreateNewUser::class);
```

The login steps are actions too, resolved from the container, so they can be extended and rebound:

| Class | What |
| --- | --- |
| `Actions\AttemptLogin` | Rate limit, lockout, password check, failed events |
| `Actions\StartAuthenticatedSession` | Two-factor challenge or login (the extension point for [login methods](alternative-login-methods.md)) |
| `Actions\LoginUser` | Logs in, regenerates the session, clears failed attempts, records `last_login_at` |
| `Actions\IssueApiToken` | The Sanctum token endpoint |
| `Actions\TwoFactor\*` | Enable, confirm, disable, recovery codes |

# Responses

`Http\Responses\LoginResponse`, `TwoFactorChallengeResponse`, `RegisterResponse` and `LogoutResponse` decide where users go. Rebind them:

```php
use Javaabu\Auth\Http\Responses\LoginResponse;

$this->app->singleton(LoginResponse::class, App\Http\Responses\LoginResponse::class);
```

`UserTypes::fromRequest($request)` gives the type inside a response.
