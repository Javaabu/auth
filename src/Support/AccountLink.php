<?php

namespace Javaabu\Auth\Support;

use Closure;
use Javaabu\Auth\UserType;

/**
 * A link in a user type's account navigation, next to Profile and Security
 */
class AccountLink
{
    /**
     * @param  string|Closure(UserType): string  $label  an English string (translated when shown) or a closure
     * @param  string  $route  the route name without the type prefix, e.g. `mobile-number` for `admin.mobile-number`
     */
    public function __construct(
        public string $key,
        public string|Closure $label,
        public string $route,
        public int $position = 100,
    ) {}

    public function label(UserType $type): string
    {
        return $this->label instanceof Closure
            ? (string) ($this->label)($type)
            : __($this->label);
    }

    /**
     * Whether the type has the link's route, so the link is shown
     */
    public function isAvailableFor(UserType $type): bool
    {
        return $type->hasRoute($this->route);
    }

    /**
     * What the navigation shows
     *
     * @return array{
     *     key: string,
     *     label: string,
     *     url: string,
     *     active: bool
     * }
     */
    public function toArray(UserType $type): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label($type),
            'url' => $type->route($this->route),
            'active' => request()->routeIs($type->routeNameFor($this->route)),
        ];
    }
}
