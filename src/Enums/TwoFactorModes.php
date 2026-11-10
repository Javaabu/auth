<?php

namespace Javaabu\Auth\Enums;

/**
 * Whether a user type offers two-factor authentication
 */
enum TwoFactorModes: string
{
    /** Users can't turn it on */
    case OFF = 'off';

    /** Users choose to turn it on */
    case OPTIONAL = 'optional';

    /** Users must set it up before they can use the app */
    case REQUIRED = 'required';

    public function label(): string
    {
        return match ($this) {
            self::OFF => __('Off'),
            self::OPTIONAL => __('Optional'),
            self::REQUIRED => __('Required'),
        };
    }
}
