<?php

namespace Javaabu\Auth\Enums;

trait EnumsTrait
{
    protected static array $labels = [];

    /**
     * Initialize labels
     * @return void
     */
    protected static function initLabels(): void
    {
        static::$labels = [];
    }

    /**
     * Get label for key
     *
     * @param $key
     * @return string
     */
    public static function getLabel($key): string
    {
        return isset(static::getLabels()[$key]) ? trans(static::getLabels()[$key]) : '';
    }

    /**
     * Get type labels
     * @return array
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
     *
     * @return array
     */
    public static function getKeys(): array
    {
        return array_keys(static::getLabels());
    }

    /**
     * Get label for key
     *
     * @param $key
     * @return string
     */
    public static function getSlug($key): string
    {
        return static::$slugs[$key] ?? '';
    }

    /**
     * Check if is a valid key
     *
     * @param $key
     * @return bool
     */
    public static function isValidKey($key): bool
    {
        return array_key_exists($key, self::getLabels());
    }
}
