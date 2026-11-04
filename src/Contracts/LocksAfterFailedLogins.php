<?php

namespace Javaabu\Auth\Contracts;

/**
 * A user whose account locks after too many failed logins in a row, until the password is reset
 */
interface LocksAfterFailedLogins
{
    public function isLockedOut(): bool;

    public function incrementLoginAttempts(): void;

    public function resetLoginAttempts(): void;
}
