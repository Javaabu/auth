<?php

namespace Javaabu\Auth\Support;

use Javaabu\Auth\Contracts\HasAccountStatus;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins;

/**
 * Whether a signed-in user may use the app, and why not
 */
class AccountState
{
    public static function inactiveMessage(mixed $user): ?string
    {
        if ($user instanceof LocksAfterFailedLogins && $user->isLockedOut()) {
            return __('Your account has been locked due to too many failed logins. Reset your password to unlock it.');
        }

        if ($user instanceof HasAccountStatus && ! $user->hasActiveAccount()) {
            return $user->accountStatusMessage();
        }

        return null;
    }

    public static function isActive(mixed $user): bool
    {
        return static::inactiveMessage($user) === null;
    }
}
