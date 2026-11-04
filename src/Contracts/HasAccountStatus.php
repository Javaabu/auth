<?php

namespace Javaabu\Auth\Contracts;

/**
 * A user whose account can be pending, approved or banned
 */
interface HasAccountStatus
{
    /** Whether the user may use the app */
    public function hasActiveAccount(): bool;

    /** Why the user can't use the app */
    public function accountStatusMessage(): string;
}
