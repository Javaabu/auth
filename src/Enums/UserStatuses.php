<?php

namespace Javaabu\Auth\Enums;

enum UserStatuses: string
{
    case APPROVED = 'approved';
    case PENDING = 'pending';
    case BANNED = 'banned';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => __('Approved'),
            self::PENDING => __('Pending'),
            self::BANNED => __('Banned'),
        };
    }

    /**
     * What a user with this status is told when they can't use their account
     */
    public function message(): string
    {
        return match ($this) {
            self::APPROVED => __('Your account is approved.'),
            self::PENDING => __('Your account needs to be approved before you can access it.'),
            self::BANNED => __('Your account has been banned.'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::APPROVED => 'success',
            self::PENDING => 'info',
            self::BANNED => 'danger',
        };
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }
}
