<?php

namespace Javaabu\Auth\Enums;

trait NativeEnumsTrait
{
    public function getLabel(): string
    {
        return self::labels()[$this->value];
    }

    public static function getLabelFromKey(string $key): string
    {
        return self::labels()[$key] ?? '';
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'value');
    }
}
