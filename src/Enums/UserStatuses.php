<?php

namespace Javaabu\Auth\Enums;

enum UserStatuses: string implements IsEnum
{
    use NativeEnumsTrait;

    case APPROVED = 'approved';
    case PENDING = 'pending';
    case BANNED = 'banned';

    /**
     * Initialize Messages
     */
    public static function messages(): array
    {
        return [
            self::APPROVED->value => __('Your account is approved.'),
            self::PENDING->value  => __('Your account needs to be approved before you can access it.'),
            self::BANNED->value   => __('Your account has been banned.'),
        ];
    }

    public function getMessage(): string
    {
        return self::messages()[$this->value];
    }

    public static function getMessageFromKey(string $key): string
    {
        return self::messages()[$key] ?? '';
    }

    public static function labels(): array
    {
        return [
            self::APPROVED->value => __("Approved"),
            self::PENDING->value  => __("Pending"),
            self::BANNED->value   => __("Banned"),
        ];
    }
}
