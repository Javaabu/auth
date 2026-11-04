<?php

namespace Javaabu\Auth\Concerns;

/**
 * A `require_password_update` column: users who have it must choose a new password first
 */
trait RequiresPasswordUpdate
{
    public function initializeRequiresPasswordUpdate(): void
    {
        $this->mergeCasts(['require_password_update' => 'boolean']);
    }

    public function requiresPasswordUpdate(): bool
    {
        return (bool) $this->require_password_update;
    }

    public function requirePasswordUpdate(): void
    {
        $this->forceFill(['require_password_update' => true])->save();
    }

    public function clearRequirePasswordUpdate(): void
    {
        $this->forceFill(['require_password_update' => false]);
    }
}
