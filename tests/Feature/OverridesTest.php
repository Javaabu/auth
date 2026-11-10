<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Contracts\CreatesNewUsers;
use Javaabu\Auth\Tests\Fixtures\CustomLoginController;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserType;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

class OverridesTest extends TestCase
{
    protected function usesCustomLoginController($app): void
    {
        $app['config']->set('javaabu-auth.controllers.login', CustomLoginController::class);
    }

    #[Test]
    #[DefineEnvironment('usesCustomLoginController')]
    public function controllers_can_be_replaced_from_config(): void
    {
        $this->get('/login')->assertOk()->assertSee('custom login for user');
        $this->get('/admin/login')->assertOk()->assertSee('custom login for admin');
    }

    #[Test]
    public function actions_can_be_replaced_in_the_container(): void
    {
        $this->app->bind(CreatesNewUsers::class, fn () => new class implements CreatesNewUsers
        {
            public function create(UserType $type, array $input): Model
            {
                return User::factory()->create(['name' => 'ކަސްޓަމް', 'email' => $input['email']]);
            }
        });

        $this->post('/register', ['email' => 'custom@example.com'])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'custom@example.com', 'name' => 'ކަސްޓަމް']);
    }
}
