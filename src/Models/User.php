<?php

namespace Javaabu\Auth\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Javaabu\Auth\Concerns\DetectsGmailAliases;
use Javaabu\Auth\Concerns\HasAccountStatus;
use Javaabu\Auth\Concerns\HasPasskeys;
use Javaabu\Auth\Concerns\HasSessions;
use Javaabu\Auth\Concerns\HasUserType;
use Javaabu\Auth\Concerns\LocksAfterFailedLogins;
use Javaabu\Auth\Concerns\RequiresPasswordUpdate;
use Javaabu\Auth\Concerns\TwoFactorAuthenticatable;
use Javaabu\Auth\Contracts\HasAccountStatus as HasAccountStatusContract;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins as LocksAfterFailedLoginsContract;
use Javaabu\Auth\Contracts\RequiresPasswordUpdate as RequiresPasswordUpdateContract;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Laravel\Passkeys\Contracts\PasskeyUser;

/**
 * A user model with every feature of the package: extend it for each user type, or use the traits yourself.
 * Its table needs the columns of Javaabu\Auth\Schema\UserSchema::columns().
 */
abstract class User extends Authenticatable implements HasAccountStatusContract, LocksAfterFailedLoginsContract, MustVerifyEmail, PasskeyUser, RequiresPasswordUpdateContract, TwoFactorAuthenticatableContract
{
    use DetectsGmailAliases;
    use HasAccountStatus;
    use HasPasskeys;
    use HasSessions;
    use HasUserType;
    use LocksAfterFailedLogins;
    use Notifiable;
    use RequiresPasswordUpdate;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Whether the user may use the app: approved, not locked, and with a verified email when the type needs one
     */
    public function isActive(): bool
    {
        if (! $this->hasActiveAccount() || $this->isLockedOut()) {
            return false;
        }

        return $this->hasVerifiedEmail() || ! $this->userType()->hasFeature('email_verification');
    }
}
