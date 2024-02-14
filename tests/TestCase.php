<?php

namespace Javaabu\Auth\Tests;

use Illuminate\Support\Facades\Route;
use Javaabu\Auth\AuthServiceProvider;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Models\User;
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

        $this->app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'database' => env('DB_DATABASE'),
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '3306'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD', ''),
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [AuthServiceProvider::class];
    }

    protected function registerTestRoute(string $uri, string $controller, $function, string $method = 'get'): void
    {
        Route::middleware('web')
            ->group(function () use ($uri, $controller, $function, $method) {
                Route::$method($uri, [$controller, $function]);
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
        $user = User::where('email', 'admin@example.com')->first();

        if (! $user) {
            $user = new User();
            $user->name = 'John Doe';
            $user->email = 'admin@example.com';
            $user->email_verified_at = now();
            $user->password = bcrypt('password');
            $user->status = UserStatuses::APPROVED;
            $user->save();
        }
    }
}
