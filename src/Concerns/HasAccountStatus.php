<?php

namespace Javaabu\Auth\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Javaabu\Auth\Enums\UserStatuses;

/**
 * Pending, approved and banned accounts, in a `status` column cast to UserStatuses.
 * Add `'status' => UserStatuses::class` to the model's casts.
 *
 * @property UserStatuses $status
 */
trait HasAccountStatus
{
    public function initializeHasAccountStatus(): void
    {
        $this->mergeCasts(['status' => UserStatuses::class]);

        if (! array_key_exists('status', $this->attributes)) {
            $this->attributes['status'] = UserStatuses::PENDING->value;
        }
    }

    public function hasActiveAccount(): bool
    {
        return $this->status === UserStatuses::APPROVED;
    }

    public function accountStatusMessage(): string
    {
        return $this->status->message();
    }

    public function approve(bool $save = false): void
    {
        $this->updateAccountStatus(UserStatuses::APPROVED, $save);
    }

    public function markAsPending(bool $save = false): void
    {
        $this->updateAccountStatus(UserStatuses::PENDING, $save);
    }

    public function ban(bool $save = false): void
    {
        $this->updateAccountStatus(UserStatuses::BANNED, $save);
    }

    public function isApproved(): bool
    {
        return $this->status === UserStatuses::APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatuses::PENDING;
    }

    public function isBanned(): bool
    {
        return $this->status === UserStatuses::BANNED;
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), UserStatuses::APPROVED->value);
    }

    public function scopePending(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), UserStatuses::PENDING->value);
    }

    public function scopeBanned(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), UserStatuses::BANNED->value);
    }

    protected function updateAccountStatus(UserStatuses $status, bool $save): void
    {
        $this->status = $status;

        if ($save) {
            $this->save();
        }
    }
}
