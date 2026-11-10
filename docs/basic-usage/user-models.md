---
title: The user model
sidebar_position: 4
---

`Javaabu\Auth\Models\User` has every feature. Extend it once per user type:

```php
namespace App\Models;

use Javaabu\Auth\Models\User as AuthUser;

class Admin extends AuthUser
{
    protected $table = 'admins';
}
```

It extends Laravel's `Illuminate\Foundation\Auth\User`, uses `SoftDeletes` and `Notifiable`, hashes `password`, and implements `MustVerifyEmail`, `Laravel\Passkeys\Contracts\PasskeyUser` and the package's contracts.

# Picking traits yourself

| Trait | Contract | Columns | What |
| --- | --- | --- | --- |
| `Concerns\HasUserType` | | | `userType()`; password reset and verification emails link to the type's routes |
| `Concerns\HasAccountStatus` | `Contracts\HasAccountStatus` | `status` | Pending, approved and banned accounts (`UserStatuses`) |
| `Concerns\LocksAfterFailedLogins` | `Contracts\LocksAfterFailedLogins` | `login_attempts` | Locks the account after `max_login_attempts` failed logins in a row, until the password is reset |
| `Concerns\RequiresPasswordUpdate` | `Contracts\RequiresPasswordUpdate` | `require_password_update` | Makes the user choose a new password before continuing |
| `Concerns\TwoFactorAuthenticatable` | `Contracts\TwoFactorAuthenticatable` | `two_factor_*` | TOTP two-factor authentication |
| `Concerns\HasPasskeys` | `Laravel\Passkeys\Contracts\PasskeyUser` | (the `passkeys` table) | Passkeys |
| `Concerns\HasSessions` | | (the `sessions` table) | `sessions()` and `deleteOtherSessionRecords()` with the `multi_auth_database` driver |
| `Concerns\DetectsGmailAliases` | | | `hasGmailAlias()` scope, see [Unique Gmail rule](unique-gmail-rule.md) |

Each trait does nothing unless the model implements the matching contract, so a model without `HasAccountStatus` simply has no status checks.

```php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Javaabu\Auth\Concerns\HasPasskeys;
use Javaabu\Auth\Concerns\HasUserType;
use Javaabu\Auth\Concerns\TwoFactorAuthenticatable;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Laravel\Passkeys\Contracts\PasskeyUser;

class User extends Authenticatable implements MustVerifyEmail, PasskeyUser, TwoFactorAuthenticatableContract
{
    use HasPasskeys;
    use HasUserType;
    use Notifiable;
    use TwoFactorAuthenticatable;
}
```

# Account status

```php
use Javaabu\Auth\Enums\UserStatuses;

$user->approve(save: true);
$user->markAsPending(save: true);
$user->ban(save: true);

$user->isApproved();
$user->hasActiveAccount();      // approved
$user->accountStatusMessage();  // "Your account has been banned."

User::approved()->get();
User::pending()->get();
User::banned()->get();
User::lockedOut()->get();
User::notLockedOut()->get();

UserStatuses::labels();         // ['approved' => 'Approved', …], translated
```

New users start as `pending`. Pending and banned users can log in (so they can verify their email), but `auth.type` sends them to the `account.inactive` page until they're approved. Approve users when they register by setting the status in your [CreatesNewUsers action](../advanced-usage/actions.md).

# Lockout and required password changes

```php
$user->isLockedOut();
$user->resetLoginAttempts();     // unlock

$user->requirePasswordUpdate();  // saves; asked on next request
$user->requiresPasswordUpdate();
```

Resetting the password unlocks the account and clears a required password change.
