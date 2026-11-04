<?php

namespace Javaabu\Auth;

use Closure;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Javaabu\Auth\Contracts\LoginMethod;
use Javaabu\Auth\Enums\TwoFactorModes;

/**
 * One kind of user (e.g. admins or public users) with its own model, guard,
 * password broker, routes, views and features
 */
class UserType
{
    public const string REGISTRATION = 'registration';

    public const string PASSWORD_RESET = 'password_reset';

    public const string EMAIL_VERIFICATION = 'email_verification';

    public const string PASSWORD_CONFIRMATION = 'password_confirmation';

    public const string PROFILE = 'profile';

    public const string DELETE_ACCOUNT = 'delete_account';

    public const string SESSIONS = 'sessions';

    public const string PASSKEYS = 'passkeys';

    public const string API_TOKENS = 'api_tokens';

    /** Features a new user type has unless it turns them off */
    public const array DEFAULT_FEATURES = [
        self::REGISTRATION => true,
        self::PASSWORD_RESET => true,
        self::EMAIL_VERIFICATION => true,
        self::PASSWORD_CONFIRMATION => true,
        self::PROFILE => true,
        self::DELETE_ACCOUNT => false,
        self::SESSIONS => true,
        self::PASSKEYS => false,
        self::API_TOKENS => false,
    ];

    /** @var class-string<Model> */
    protected string $model;

    protected string $guard;

    protected string $passwords;

    protected string $route_name;

    protected string|Closure $home = '/';

    protected ?string $views = null;

    /** @var array<string, string> */
    protected array $layouts = [];

    protected string $username = 'email';

    /** @var array<string, bool> */
    protected array $features = self::DEFAULT_FEATURES;

    protected TwoFactorModes $two_factor = TwoFactorModes::OPTIONAL;

    protected ?int $max_login_attempts = null;

    /** @var array<string, class-string<LoginMethod>|LoginMethod> */
    protected array $login_methods = [];

    public function __construct(
        protected string $name,
    ) {
        if (! preg_match('/^[a-z][a-z0-9_-]*$/', $name)) {
            throw new InvalidArgumentException("User type names are lowercase letters, numbers, dashes and underscores; got `{$name}`.");
        }

        $this->guard = $name;
        $this->passwords = str($name)->plural()->toString();
        $this->route_name = $name;
    }

    /**
     * Build a user type from its config array
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(string $name, array $config): static
    {
        $type = new static($name);

        foreach ($config as $key => $value) {
            match ($key) {
                'model' => $type->model($value),
                'guard' => $type->guard($value),
                'passwords' => $type->passwords($value),
                'route_name' => $type->routeName($value),
                'home' => $type->home($value),
                'views' => $type->views($value),
                'layouts' => $type->layouts($value),
                'username' => $type->username($value),
                'features' => $type->features($value),
                'two_factor' => $type->twoFactor($value),
                'max_login_attempts' => $type->maxLoginAttempts($value),
                'login_methods' => $type->loginMethods($value),
                default => throw new InvalidArgumentException("Unknown option `{$key}` for the `{$name}` user type."),
            };
        }

        return $type;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param  class-string<Model>  $model
     */
    public function model(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    /** @return class-string<Model> */
    public function modelClass(): string
    {
        if (isset($this->model)) {
            return $this->model;
        }

        $provider = config("auth.guards.{$this->guard}.provider");
        $model = $provider ? config("auth.providers.{$provider}.model") : null;

        if (! $model) {
            throw new InvalidArgumentException("Give the `{$this->name}` user type a model, or a guard with an eloquent provider.");
        }

        return $model;
    }

    public function newModel(): Model
    {
        $model = $this->modelClass();

        return new $model;
    }

    public function guard(string $guard): static
    {
        $this->guard = $guard;

        return $this;
    }

    public function guardName(): string
    {
        return $this->guard;
    }

    public function auth(): StatefulGuard
    {
        return Auth::guard($this->guard);
    }

    public function passwords(string $broker): static
    {
        $this->passwords = $broker;

        return $this;
    }

    public function passwordBrokerName(): string
    {
        return $this->passwords;
    }

    public function broker(): PasswordBroker
    {
        return Password::broker($this->passwords);
    }

    /**
     * The prefix of the route names, e.g. `admin` for `admin.login`; empty for `login`
     */
    public function routeName(string $route_name): static
    {
        $this->route_name = trim($route_name, '.');

        return $this;
    }

    public function routeNamePrefix(): string
    {
        return $this->route_name === '' ? '' : "{$this->route_name}.";
    }

    /**
     * The full name of one of this type's routes: `login` → `admin.login`
     */
    public function routeNameFor(string $route): string
    {
        return $this->routeNamePrefix() . $route;
    }

    public function hasRoute(string $route): bool
    {
        return Route::has($this->routeNameFor($route));
    }

    /**
     * The URL of one of this type's routes
     *
     * @param  array<string, mixed>  $parameters
     */
    public function route(string $route, array $parameters = [], bool $absolute = true): string
    {
        return route($this->routeNameFor($route), $parameters, $absolute);
    }

    /**
     * Where users go after logging in: a path, a route name, or a closure given the user
     */
    public function home(string|Closure $home): static
    {
        $this->home = $home;

        return $this;
    }

    public function homeUrl(?Model $user = null): string
    {
        $home = $this->home;

        if ($home instanceof Closure) {
            return $home($user);
        }

        if (Route::has($home)) {
            return route($home);
        }

        return url($home);
    }

    /**
     * The view stack for this type's pages, `blade` or `inertia`; null uses `javaabu-auth.views`
     */
    public function views(?string $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function viewStack(): string
    {
        return $this->views ?? config('javaabu-auth.views', 'blade');
    }

    /**
     * The Blade layouts this type's pages extend: `auth` for the guest pages, `account` for the signed-in ones;
     * missing ones use `javaabu-auth.layouts`
     *
     * @param  array<string, string>  $layouts
     */
    public function layouts(array $layouts): static
    {
        $this->layouts = [...$this->layouts, ...$layouts];

        return $this;
    }

    public function layoutName(string $kind = 'auth'): string
    {
        return $this->layouts[$kind]
            ?? config("javaabu-auth.layouts.{$kind}")
            ?? "auth::layouts.{$kind}";
    }

    /**
     * The column users log in with
     */
    public function username(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function usernameColumn(): string
    {
        return $this->username;
    }

    /**
     * Turn features on or off: `['registration' => false, 'passkeys' => true]`
     *
     * @param  array<string, bool>|array<int, string>  $features
     */
    public function features(array $features): static
    {
        foreach ($features as $feature => $enabled) {
            if (is_int($feature)) {
                $feature = $enabled;
                $enabled = true;
            }

            if (! array_key_exists($feature, self::DEFAULT_FEATURES)) {
                throw new InvalidArgumentException("Unknown feature `{$feature}` for the `{$this->name}` user type.");
            }

            $this->features[$feature] = (bool) $enabled;
        }

        return $this;
    }

    public function enable(string ...$features): static
    {
        return $this->features(array_fill_keys($features, true));
    }

    public function disable(string ...$features): static
    {
        return $this->features(array_fill_keys($features, false));
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }

    /** @return array<string, bool> */
    public function allFeatures(): array
    {
        return $this->features;
    }

    public function twoFactor(TwoFactorModes|string $mode): static
    {
        $this->two_factor = $mode instanceof TwoFactorModes
            ? $mode
            : TwoFactorModes::from($mode);

        return $this;
    }

    public function twoFactorMode(): TwoFactorModes
    {
        return $this->two_factor;
    }

    public function hasTwoFactor(): bool
    {
        return $this->two_factor !== TwoFactorModes::OFF;
    }

    public function requiresTwoFactor(): bool
    {
        return $this->two_factor === TwoFactorModes::REQUIRED;
    }

    /**
     * Lock accounts after this many failed logins in a row, until the password is reset; null never locks
     */
    public function maxLoginAttempts(?int $attempts): static
    {
        $this->max_login_attempts = $attempts;

        return $this;
    }

    public function maxLoginAttemptsAllowed(): ?int
    {
        return $this->max_login_attempts ?? config('javaabu-auth.max_login_attempts');
    }

    /**
     * Ways to log in besides the password form, e.g. a mobile number and one-time code
     *
     * @param  array<int, class-string<LoginMethod>|LoginMethod>  $methods
     */
    public function loginMethods(array $methods): static
    {
        foreach ($methods as $method) {
            $this->loginMethod($method);
        }

        return $this;
    }

    /**
     * @param  class-string<LoginMethod>|LoginMethod  $method
     */
    public function loginMethod(string|LoginMethod $method): static
    {
        $instance = is_string($method) ? app($method) : $method;

        if (! $instance instanceof LoginMethod) {
            throw new InvalidArgumentException('Login methods implement ' . LoginMethod::class . '.');
        }

        $this->login_methods[$instance->key()] = $instance;

        return $this;
    }

    /** @return array<string, LoginMethod> */
    public function loginMethodsList(): array
    {
        return $this->login_methods;
    }

    /**
     * Whether a model is one of this type's users
     */
    public function owns(mixed $user): bool
    {
        return $user instanceof Model && is_a($user, $this->modelClass());
    }
}
