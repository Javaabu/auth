<?php

namespace Javaabu\Auth;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Javaabu\Auth\Support\AccountLink;

/**
 * @method static UserType register(string $name, array|Closure|null $config = null)
 * @method static bool has(string $name)
 * @method static UserType get(string $name)
 * @method static array<string, UserType> all()
 * @method static UserType default()
 * @method static UserType|null for(Model|string $user)
 * @method static UserType|null forGuard(?string $guard)
 * @method static UserType|null fromRequest(?Request $request = null)
 * @method static AccountLink accountLink(string $key, string|Closure $label, ?string $route = null, int $position = 100)
 * @method static array<string, AccountLink> accountLinks()
 * @method static void forget(string $name)
 * @method static void flush()
 *
 * @see UserTypeRegistry
 */
class UserTypes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserTypeRegistry::class;
    }
}
