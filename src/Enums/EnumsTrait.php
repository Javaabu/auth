<?php

namespace Javaabu\Auth\Enums;

trait EnumsTrait
{
    protected static array $labels = [];

    /**
     * Initialize labels
     */
    protected static function initLabels(): void
    {
        static::$labels = [];
    }

    /**
     * Get label for key
     */
    public static function getLabel($key): string
    {
        return isset(static::getLabels()[$key]) ? trans(static::getLabels()[$key]) : '';
    }

    /**
     * Get type labels
     */
    public static function getLabels(): array
    {
        //first initialize
        if (empty(static::$labels)) {
            static::initLabels();
        }

        return static::$labels;
    }

    /**
     * Get keys
     */
    public static function getKeys(): array
    {
        return array_keys(static::getLabels());
    }

    /**
     * Get label for key
     */
    public static function getSlug($key): string
    {
        return static::$slugs[$key] ?? '';
    }

    /**
     * Check if is a valid key
     */
    public static function isValidKey($key): bool
    {
        return array_key_exists($key, self::getLabels());
    }
}
