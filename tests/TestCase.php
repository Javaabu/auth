<?php

namespace Javaabu\Auth\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Javaabu\Activitylog\ActivitylogServiceProvider;
use Javaabu\Auth\AuthServiceProvider;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Models\User;
use Javaabu\Auth\Tests\Feature\Http\Controllers\HomeController;
use Javaabu\Auth\Tests\Feature\Http\Controllers\LoginController;
use Javaabu\Helpers\HelpersServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        // Set the view path to include your package's views directory
        $this->app['config']->set('view.paths', [__DIR__ . '/Feature/views']);

        $this->app['config']->set('database.default', 'mysql');

        $this->app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'database' => env('DB_DATABASE'),
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '3306'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD', ''),
            'prefix'   => '',
        ]);

        $this->registerRoutes();
    }

    protected function getPackageProviders($app)
    {
        return [
            HelpersServiceProvider::class,
            AuthServiceProvider::class,
            ActivitylogServiceProvider::class
        ];
    }

    protected function registerTestRoute(string $uri, string $controller, $function, string $method = 'get', string $name = null): void
    {
        Route::middleware('web')
            ->group(function () use ($uri, $controller, $function, $method, $name) {
                $route = Route::$method($uri, [$controller, $function]);

                if ($name) {
                    $route->name($name);
                }
            });
    }

    public function seedDatabase(): void
    {
        if (!SeedState::$seeded) {
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
        $this->registerTestRoute(
            '/login',
            LoginController::class,
            'getLoginForm',
            name: 'login'
        );

        $this->registerTestRoute(
            '/login',
            LoginController::class,
            'login',
            method: 'post');

        $this->registerTestRoute(
            '/',
            HomeController::class,
            'index',
            name: 'home'
        );
    }
}
