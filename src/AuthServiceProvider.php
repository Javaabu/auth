<?php

namespace Javaabu\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Javaabu\Auth\Actions\CreateNewUser;
use Javaabu\Auth\Actions\DeleteUser;
use Javaabu\Auth\Actions\ResetUserPassword;
use Javaabu\Auth\Actions\UpdateUserPassword;
use Javaabu\Auth\Actions\UpdateUserProfileInformation;
use Javaabu\Auth\Contracts\CreatesNewUsers;
use Javaabu\Auth\Contracts\DeletesUsers;
use Javaabu\Auth\Contracts\ResetsUserPasswords;
use Javaabu\Auth\Contracts\UpdatesUserPasswords;
use Javaabu\Auth\Contracts\UpdatesUserProfileInformation;
use Javaabu\Auth\Http\Middleware\Authenticate;
use Javaabu\Auth\Http\Middleware\EnsureAccountIsActive;
use Javaabu\Auth\Http\Middleware\EnsureEmailIsVerified;
use Javaabu\Auth\Http\Middleware\EnsurePasswordIsUpdated;
use Javaabu\Auth\Http\Middleware\EnsureTwoFactorIsEnabled;
use Javaabu\Auth\Http\Middleware\RedirectIfAuthenticated;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Http\Middleware\SetUserType;
use Javaabu\Auth\Passkeys\Passkey;
use Javaabu\Auth\Passkeys\VerifyPasskey;
use Javaabu\Auth\Routing\AuthRoutes;
use Javaabu\Auth\Sessions\MultiAuthDatabaseSessionHandler;
use Javaabu\Auth\Support\ActivityLogger;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Manifest\Concerns\RegistersAssets;
use Javaabu\Manifest\Package;
use Laravel\Passkeys\Actions\VerifyPasskey as BaseVerifyPasskey;
use Laravel\Passkeys\Passkeys;
use PragmaRX\Google2FA\Google2FA;

class AuthServiceProvider extends ServiceProvider
{
    use RegistersAssets;

    /** Route middleware the package registers */
    public const array MIDDLEWARE = [
        'auth.type' => Authenticate::class,
        'auth.guest' => RedirectIfAuthenticated::class,
        'auth.user-type' => SetUserType::class,
        'auth.active' => EnsureAccountIsActive::class,
        'auth.verified' => EnsureEmailIsVerified::class,
        'auth.password-updated' => EnsurePasswordIsUpdated::class,
        'auth.two-factor' => EnsureTwoFactorIsEnabled::class,
        'auth.password.confirm' => RequirePassword::class,
    ];

    /** Default implementations of the actions apps can replace */
    public const array ACTIONS = [
        CreatesNewUsers::class => CreateNewUser::class,
        ResetsUserPasswords::class => ResetUserPassword::class,
        UpdatesUserPasswords::class => UpdateUserPassword::class,
        UpdatesUserProfileInformation::class => UpdateUserProfileInformation::class,
        DeletesUsers::class => DeleteUser::class,
    ];

    public static bool $runs_migrations = true;

    /**
     * Stop the package from loading its migrations, e.g. after publishing them
     */
    public static function ignoreMigrations(): void
    {
        static::$runs_migrations = false;
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/javaabu-auth.php', 'javaabu-auth');

        $this->app->singleton(UserTypeRegistry::class);

        foreach (self::ACTIONS as $contract => $action) {
            $this->app->bindIf($contract, $action);
        }

        $this->app->singleton(TwoFactorProvider::class, function (Application $app) {
            return new TwoFactorProvider($app->make(Google2FA::class), $app->make('cache')->store());
        });

        $this->app->bind(BaseVerifyPasskey::class, VerifyPasskey::class);

        // The package registers passkey routes per user type
        Passkeys::ignoreRoutes();
        Passkeys::usePasskeyModel(Passkey::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'auth');

        if (static::$runs_migrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->registerFrontendAssets();
        $this->registerMiddleware();
        $this->registerRouteMacros();
        $this->registerRateLimiters();
        $this->registerSessionDriver();
        $this->registerGuestRedirects();

        $this->app->make(ActivityLogger::class)->register();
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/javaabu-auth.php' => config_path('javaabu-auth.php'),
        ], 'auth-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/auth'),
        ], 'auth-views');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/auth'),
        ], 'auth-translations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'auth-migrations');
    }

    /**
     * The Inertia pages (auth::Login, …), the code apps can import as `@javaabu/auth`,
     * and the scripts the Blade pages load
     */
    protected function registerFrontendAssets(): void
    {
        $this->registerAssets('auth', __DIR__ . '/../resources', function (Package $package) {
            $package
                ->script('auth', 'js/auth.ts')
                ->script('passkeys', 'js/passkeys.ts')
                ->js()
                ->pages()
                ->npm([
                    '@javaabu/forms' => 'github:Javaabu/react-forms#next-gen',
                    '@javaabu/onu' => '^0.2.1',
                ], peer_dependencies: [
                    'react' => '^18.3 || ^19.0',
                    'react-dom' => '^18.3 || ^19.0',
                    '@inertiajs/react' => '^2.0 || ^3.0',
                ]);
        });
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        foreach (self::MIDDLEWARE as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('authRoutes', function (?string $type = null, array $options = []) {
            AuthRoutes::register($this, $type, $options);
        });
    }

    protected function registerRateLimiters(): void
    {
        $limit = fn (string $key, int $default) => (int) config("javaabu-auth.rate_limits.{$key}", $default);

        RateLimiter::for('auth.passkeys', function (Request $request) use ($limit) {
            return Limit::perMinute($limit('passkeys', 10))
                ->by(($request->input('credential.id') ?: $request->session()->getId()) . '|' . $request->ip());
        });

        RateLimiter::for('auth.password-reset', function (Request $request) use ($limit) {
            return Limit::perMinute($limit('password_reset', 6))->by($request->ip());
        });

        RateLimiter::for('auth.verification', function (Request $request) use ($limit) {
            return Limit::perMinute($limit('verification', 6))->by($request->ip());
        });

        RateLimiter::for('auth.api-tokens', function (Request $request) use ($limit) {
            return Limit::perMinute($limit('api_tokens', 6))->by($request->ip());
        });
    }

    protected function registerSessionDriver(): void
    {
        Session::extend('multi_auth_database', function (Application $app) {
            $connection = $app->make('db')->connection(config('session.connection'));

            return new MultiAuthDatabaseSessionHandler(
                $connection,
                config('session.table', 'sessions'),
                config('session.lifetime'),
                $app,
            );
        });
    }

    /**
     * Guests on a user type's guard go to that type's login page, not `route('login')`
     */
    protected function registerGuestRedirects(): void
    {
        $this->callAfterResolving(ExceptionHandler::class, function (ExceptionHandler $handler) {
            if (! $handler instanceof Handler) {
                return;
            }

            $handler->renderable(function (AuthenticationException $exception, Request $request) {
                if ($request->expectsJson()) {
                    return null;
                }

                // Respect a redirect the app chose, but not Laravel's default `route('login')`
                $redirect_to = $exception->redirectTo($request);

                if ($redirect_to && (! Route::has('login') || $redirect_to !== route('login'))) {
                    return null;
                }

                foreach ($exception->guards() as $guard) {
                    $type = UserTypes::forGuard($guard);

                    if ($type?->hasRoute('login')) {
                        return redirect()->guest($type->route('login'));
                    }
                }

                return null;
            });
        });
    }
}
