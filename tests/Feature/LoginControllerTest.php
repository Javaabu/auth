<?php

namespace Javaabu\Auth\Tests\Feature;

use Javaabu\Auth\Tests\Feature\Http\Controllers\LoginController;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use InteractsWithDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
        $this->seedDefaultUsers();
    }

    /** @test */
    public function it_can_show_the_login_form_page(): void
    {
        $this->withoutExceptionHandling();
        $this->registerTestRoute(
            '/login',
            LoginController::class,
            'getLoginForm'
        );

        $this->get('/login')
            ->assertStatus(200)
            ->assertViewIs('login');
    }

    /** @test */
    public function it_can_login_a_user(): void
    {
        $this->withoutExceptionHandling();

        $this->registerTestRoute(
            '/login',
            LoginController::class,
            'login',
            method: 'post');

        $this->post('/login', [
            'email'    => 'admin@example.com',
            'password' => 'password',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/');
    }
}
