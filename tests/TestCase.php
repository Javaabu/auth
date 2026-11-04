<?php

namespace Javaabu\Auth\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Javaabu\Auth\AuthServiceProvider;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Manifest\Facades\Manifest;
use Javaabu\Manifest\ManifestServiceProvider;
use Javaabu\Permissions\PermissionsServiceProvider;
use Laravel\Passkeys\PasskeysServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Manifest::fake();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActivitylogServiceProvider::class,
            PermissionServiceProvider::class,
            PermissionsServiceProvider::class,
            ManifestServiceProvider::class,
            FormsServiceProvider::class,
            PasskeysServiceProvider::class,
            AuthServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];

        $config->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');
        $config->set('app.url', 'http://localhost');
        $config->set('session.serialization', 'php');
        $config->set('view.paths', [__DIR__ . '/views', ...$config->get('view.paths', [])]);

        $config->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'web_admin' => ['driver' => 'session', 'provider' => 'admins'],
        ]);

        $config->set('auth.providers', [
            'users' => ['driver' => 'eloquent', 'model' => User::class],
            'admins' => ['driver' => 'eloquent', 'model' => Admin::class],
        ]);

        $config->set('auth.passwords', [
            'users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 0],
            'admins' => ['provider' => 'admins', 'table' => 'admin_password_reset_tokens', 'expire' => 60, 'throttle' => 0],
        ]);

        $config->set('javaabu-auth.user_types', [
            'user' => [
                'guard' => 'web',
                'passwords' => 'users',
                'route_name' => '',
                'home' => '/dashboard',
                'features' => ['passkeys' => true, 'delete_account' => true],
            ],
            'admin' => [
                'guard' => 'web_admin',
                'passwords' => 'admins',
                'home' => '/admin',
                'features' => ['registration' => false, 'passkeys' => true],
            ],
        ]);

        $config->set('javaabu-auth.layouts', [
            'auth' => 'layouts.app',
            'account' => 'layouts.app',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database');
    }

    protected function defineRoutes($router): void
    {
        $this->registerAuthRoutes();
    }

    protected function registerAuthRoutes(): void
    {
        Route::authRoutes('user');
        Route::authRoutes('admin', ['prefix' => 'admin']);

        Route::middleware(['web', 'auth.type:user'])->get('dashboard', fn () => 'user dashboard')->name('dashboard');
        Route::middleware(['web', 'auth.type:admin'])->get('admin', fn () => 'admin dashboard')->name('admin.home');
    }

    protected function userType(string $name = 'user'): UserType
    {
        return UserTypes::get($name);
    }
}
