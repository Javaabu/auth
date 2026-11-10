<?php

namespace Javaabu\Auth;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Javaabu\Auth\Support\AccountLink;

/**
 * The user types of the app, from `config('javaabu-auth.user_types')` and `UserTypes::register()`
 */
class UserTypeRegistry
{
    /** The route action key that holds the user type of a route */
    public const string ROUTE_ACTION = 'auth_user_type';

    /** @var array<string, UserType> */
    protected array $types = [];

    protected bool $loaded_config = false;

    /** @var array<string, AccountLink> */
    protected array $account_links = [];

    /**
     * Add a user type, or change one: `register('admin', ['guard' => 'web_admin'])`
     *
     * @param  array<string, mixed>|Closure(UserType): mixed|null  $config
     */
    public function register(string $name, array|Closure|null $config = null): UserType
    {
        $this->loadConfig();

        $type = is_array($config)
            ? UserType::fromArray($name, $config)
            : ($this->types[$name] ?? new UserType($name));

        if ($config instanceof Closure) {
            $config($type);
        }

        return $this->types[$name] = $type;
    }

    public function has(string $name): bool
    {
        $this->loadConfig();

        return isset($this->types[$name]);
    }

    public function get(string $name): UserType
    {
        $this->loadConfig();

        return $this->types[$name]
            ?? throw new InvalidArgumentException("No user type named `{$name}`. Add it to javaabu-auth.user_types or register it with UserTypes::register().");
    }

    /** @return array<string, UserType> */
    public function all(): array
    {
        $this->loadConfig();

        return $this->types;
    }

    /**
     * The first registered user type
     */
    public function default(): UserType
    {
        $types = $this->all();

        if (! $types) {
            throw new InvalidArgumentException('No user types are registered.');
        }

        return reset($types);
    }

    /**
     * The user type of a model or model class; exact classes win over parent classes
     *
     * @param  Model|class-string<Model>  $user
     */
    public function for(Model|string $user): ?UserType
    {
        $class = is_string($user) ? $user : $user::class;

        foreach ($this->all() as $type) {
            if ($type->modelClass() === $class) {
                return $type;
            }
        }

        foreach ($this->all() as $type) {
            if (is_a($class, $type->modelClass(), true)) {
                return $type;
            }
        }

        return null;
    }

    public function forGuard(?string $guard): ?UserType
    {
        $guard ??= config('auth.defaults.guard');

        foreach ($this->all() as $type) {
            if ($type->guardName() === $guard) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The user type of the current route, from `Route::authRoutes()` or the `user-type` middleware
     */
    public function fromRequest(?Request $request = null): ?UserType
    {
        $request ??= request();

        $name = $request->attributes->get(self::ROUTE_ACTION)
            ?? $request->route()?->getAction(self::ROUTE_ACTION);

        return $name ? $this->get($name) : null;
    }

    /**
     * Add a link to the account navigation of every user type that has the route,
     * e.g. from a package whose page each type can register
     *
     * @param  string|Closure(UserType): string  $label  an English string (translated when shown) or a closure
     * @param  string|null  $route  the route name without the type prefix; defaults to the key
     */
    public function accountLink(string $key, string|Closure $label, ?string $route = null, int $position = 100): AccountLink
    {
        return $this->account_links[$key] = new AccountLink($key, $label, $route ?? $key, $position);
    }

    /** @return array<string, AccountLink> */
    public function accountLinks(): array
    {
        return $this->account_links;
    }

    public function forget(string $name): void
    {
        unset($this->types[$name]);
    }

    public function flush(): void
    {
        $this->types = [];
        $this->account_links = [];
        $this->loaded_config = true;
    }

    protected function loadConfig(): void
    {
        if ($this->loaded_config) {
            return;
        }

        $this->loaded_config = true;

        foreach (config('javaabu-auth.user_types', []) as $name => $config) {
            $this->types[$name] = UserType::fromArray($name, $config);
        }
    }
}
