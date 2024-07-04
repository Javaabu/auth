<?php

namespace Javaabu\Auth\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Javaabu\Activitylog\ActivitylogServiceProvider;
use Javaabu\Auth\AuthServiceProvider;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Tests\Feature\Http\Controllers\ConfirmPasswordController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\ForgotPasswordController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\HomeController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\LoginController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\RegisterController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\ResetPasswordController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\UpdatePasswordController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\VerificationController;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Helpers\HelpersServiceProvider;
use Javaabu\Schema\SchemaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        // Set the view path to include your package's views directory
        $this->app['config']->set('view.paths', [__DIR__.'/Feature/views']);

        $this->app['config']->set('app.api_prefix', 'api/v1');

        $this->app['config']->set('auth.providers.users.model', User::class);

        // set password reset
        $this->app['config']->set('auth.passwords.users', [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ]);

        $this->registerRoutes();

        Mail::fake();
        Notification::fake();
    }

    protected function getPackageProviders($app)
    {
        return [
            SchemaServiceProvider::class,
            HelpersServiceProvider::class,
            EventServiceProvider::class,
            AuthServiceProvider::class,
            ActivitylogServiceProvider::class,
        ];
    }

    protected function registerTestRoute(
        string $uri,
        string $controller,
        $function,
        string $method = 'get',
        ?string $name = null,
        array $middlewares = ['web']
    ): void {
        Route::middleware(array_merge(['web'], $middlewares ?? []))
            ->group(function () use ($uri, $controller, $function, $method, $name) {
                $route = Route::$method($uri, [$controller, $function]);

                if ($name) {
                    $route->name($name);
                }
            });
    }

    public function seedDatabase(): void
    {
        if (! SeedState::$seeded) {
            $this->seedDefaultUsers();

            SeedState::$seeded = true;
        }
    }

    protected function seedDefaultUsers(): void
    {
        Model::unguard();

        User::firstOrCreate([
            'name' => 'John Doe',
            'email' => 'user@example.com',
        ], [
            'password' => 'password',
            'status' => UserStatuses::APPROVED,
            'email_verified_at' => now(),
        ]);

        Model::reguard();
    }

    protected function getUser(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'middleware' => ['web', 'auth:web', 'active:web', 'password-update-not-required:web'],
        ], function () {
            Route::get('/test', function () {
                return 'Test';
            })->middleware('password.confirm:password.confirm');
        });

        Route::group([
            'middleware' => ['web'],
        ], function () {
            Route::post('/session-test', function () {
                session()->put('test_key', 'test_value');

                return 'Done!';
            });
        });

        $this->registerTestRoute(
            '/login',
            LoginController::class,
            'getLoginForm',
            name: 'login'
        );

        $this->registerTestRoute(
            '/logout',
            LoginController::class,
            'logout',
            method: 'post',
            name: 'logout'
        );

        $this->registerTestRoute(
            '/login',
            LoginController::class,
            'login',
            method: 'post'
        );

        $this->registerTestRoute(
            '/register',
            RegisterController::class,
            'register',
            method: 'post'
        );

        $this->registerTestRoute(
            '/register',
            RegisterController::class,
            'showRegistrationForm',
        );

        $this->registerTestRoute(
            '/',
            HomeController::class,
            'index',
            name: 'home',
            middlewares: ['auth:web', 'active:web', 'password-update-not-required:web']
        );

        // Email Verification
        Route::group([
            'namespace' => 'Auth',
            'prefix' => 'verify',
            'as' => 'verification.',
        ], function () {
            Route::get('/', [VerificationController::class, 'show'])->name('notice');
            Route::post('email/resend', [VerificationController::class, 'resend'])->name('resend');
            Route::get('email/{id}/{hash}', [VerificationController::class, 'verify'])->name('verify');
        });

        Route::group([
            'prefix' => 'password',
            'as' => 'password.',
            'middleware' => ['web'],
        ], function () {
            // Forgot Password
            Route::get('reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('request');
            Route::post('email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('email');
            Route::get('reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('reset');
            Route::post('reset', [ResetPasswordController::class, 'reset'])->name('update');

            // Confirm Password
            Route::get('confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('confirm');
            Route::post('confirm', [ConfirmPasswordController::class, 'confirm'])->name('confirm-post');

            // Password Update
            Route::get('update', [UpdatePasswordController::class, 'showPasswordUpdateForm'])->name('new-password');
            Route::post('update', [UpdatePasswordController::class, 'updatePassword'])->name('new-password-post');
        });
    }
}
