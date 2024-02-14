<?php

namespace Javaabu\Auth\Enums;

abstract class UserStatuses
{
    use EnumsTrait;

    public const APPROVED = 'approved';
    public const PENDING = 'pending';
    public const BANNED = 'banned';

    /**
     * Slugs
     */
    protected static array $slugs = [
        self::APPROVED => 'approved',
        self::PENDING => 'pending',
        self::BANNED => 'banned',
    ];

    /**
     * Messages
     */
    protected static array $messages;

    /**
     * Initialize Messages
     */
    protected static function initMessages(): void
    {
        static::$messages = [
            self::APPROVED    => __('Your account is approved.'),
            self::PENDING   => __('Your account needs to be approved before you can access it.'),
            self::BANNED    => __('Your account has been banned.'),
        ];
    }

    /**
     * Initialize labels
     */
    protected static function initLabels(): void
    {
        static::$labels = [
            self::APPROVED    => __('Approved'),
            self::PENDING   => __('Pending'),
            self::BANNED    => __('Banned'),
        ];
    }

    /**
     * Get label for key
     *
     * @param $key
     * @return string
     */
    public static function getMessage($key): string
    {
        //first initialize
        if (empty(static::$messages)) {
            static::initMessages();
        }

        return isset(static::$messages[$key]) ? trans(static::$messages[$key]) : $key;
    }
}
