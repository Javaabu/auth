<?php

namespace Javaabu\Auth\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Javaabu\Auth\UserTypes;

/**
 * Counts failed logins in a `login_attempts` column and locks the account at the
 * user type's max_login_attempts, until the password is reset
 */
trait LocksAfterFailedLogins
{
    public function initializeLocksAfterFailedLogins(): void
    {
        $this->mergeCasts(['login_attempts' => 'integer']);
    }

    public function maxLoginAttempts(): ?int
    {
        $type = UserTypes::for($this);

        return $type
            ? $type->maxLoginAttemptsAllowed()
            : config('javaabu-auth.max_login_attempts');
    }

    public function isLockedOut(): bool
    {
        $max = $this->maxLoginAttempts();

        if (! $max) {
            return false;
        }

        return ($this->login_attempts ?? 0) >= $max;
    }

    public function incrementLoginAttempts(): void
    {
        $this->forceFill(['login_attempts' => ($this->login_attempts ?? 0) + 1])->saveQuietly();
    }

    public function resetLoginAttempts(): void
    {
        if (! $this->login_attempts) {
            return;
        }

        $this->forceFill(['login_attempts' => null])->saveQuietly();
    }

    public function scopeLockedOut(Builder $query): void
    {
        $max = $this->maxLoginAttempts();

        if (! $max) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where($this->qualifyColumn('login_attempts'), '>=', $max);
    }

    public function scopeNotLockedOut(Builder $query): void
    {
        $max = $this->maxLoginAttempts();

        if (! $max) {
            return;
        }

        $query->where(function (Builder $query) use ($max) {
            $query->whereNull($this->qualifyColumn('login_attempts'))
                ->orWhere($this->qualifyColumn('login_attempts'), '<', $max);
        });
    }
}
