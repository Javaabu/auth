---
title: Managing accounts
sidebar_position: 14
---

Admins help users who are stuck: unlock a locked account, reset two-factor authentication for someone who lost their phone, make someone choose a new password, or log someone out of every device. `Route::userAccountActions()` adds these for one user type's accounts. Put them behind the admins' middleware:

```php
Route::userAccountActions('user', [
    'prefix' => 'admin/users',
    'middleware' => ['web', 'auth.type:admin'],
]);
```

| Method | URL | Name | Ability | Permission |
| --- | --- | --- | --- | --- |
| POST | `{prefix}/{account}/unlock` | `users.account.unlock` | `auth.unlock` | `unlock_users` |
| DELETE | `{prefix}/{account}/two-factor` | `users.account.reset-two-factor` | `auth.reset-two-factor` | `reset_two_factor_users` |
| POST | `{prefix}/{account}/require-password-update` | `users.account.require-password-update` | `auth.require-password-update` | `require_password_update_users` |
| POST | `{prefix}/{account}/log-out` | `users.account.log-out` | `auth.log-out` | `log_out_users` |

`{account}` is the user's route key. The name prefix defaults to the plural of the type (`users.account`); set `name` to change it. Each action answers JSON (`{"message": …}`) or redirects back with a status.

Logging out everywhere replaces the remember token, deletes the user's sessions (with the `multi_auth_database` driver) and their API tokens.

```blade
<x-forms::form :action="route('users.account.unlock', $user)">
    <x-forms::submit>{{ __('Unlock') }}</x-forms::submit>
</x-forms::form>
```

# Permissions

The package registers these four permissions for every user type (`unlock_admins`, …) through javaabu/permissions, in the type's plural group (`users`, `admins`), so they appear next to your own user permissions on the role pages. Run `php artisan permissions:sync` after installing. Limit them to some types, or turn them off:

```php
'managed_user_types' => ['user'],
'register_permissions' => false,
```

# The policy

The abilities are defined on the gate and point at `Javaabu\Auth\Policies\UserAccountPolicy`, which checks the permission of the managed type. They take the managed type and the account, so your own user policy doesn't get in the way:

```php
use Illuminate\Support\Facades\Gate;
use Javaabu\Auth\UserTypes;

Gate::allows('auth.unlock', [UserTypes::get('user'), $user]);
@can('auth.reset-two-factor', [UserTypes::get('user'), $user]) … @endcan
```

Replace it with your own class in config, extending the package's:

```php
// config/javaabu-auth.php
'policy' => App\Policies\UserAccountPolicy::class,
```

```php
namespace App\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Policies\UserAccountPolicy as BasePolicy;
use Javaabu\Auth\UserType;

class UserAccountPolicy extends BasePolicy
{
    public function logOut(Model $user, UserType $type, ?Model $account = null): bool
    {
        // Admins can't log themselves out from here
        return parent::logOut($user, $type, $account) && ! $account?->is($user);
    }
}
```

Or redefine one ability in a service provider: `Gate::define('auth.unlock', fn ($user, $type, $account) => $user->isSuperAdmin())`. The package doesn't replace abilities that are already defined.
