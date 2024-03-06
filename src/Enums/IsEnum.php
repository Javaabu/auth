<?php

namespace Javaabu\Auth\Enums;

interface IsEnum
{
    public static function labels(): array;

    public function getLabel(): string;
}
