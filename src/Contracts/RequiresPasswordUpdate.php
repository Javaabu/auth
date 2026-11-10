<?php

namespace Javaabu\Auth\Contracts;

/**
 * A user who can be made to choose a new password before using the app
 */
interface RequiresPasswordUpdate
{
    public function requiresPasswordUpdate(): bool;

    public function requirePasswordUpdate(): void;

    public function clearRequirePasswordUpdate(): void;
}
