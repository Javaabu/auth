<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
        $this->seedDefaultUsers();
    }

    /** @test */
    public function it_can_show_the_login_form_page(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertViewIs('login');
    }

    /** @test */
    public function it_can_login_a_user(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/');
    }

    /** @test */
    public function it_can_validate_the_login_inputs()
    {
        $this->post('/login', [
            'email'    => '',
            'password' => '',
        ])
            ->assertSessionHasErrors('password', 'email');
    }

    /** @test */
    public function it_does_not_allow_a_user_to_be_logged_in_using_an_invalid_password()
    {
        $this->post('/login', [
            'email'    => 'user@example.com',
            'password' => '9876544',
        ])
            ->assertSessionHasErrors('email');

        $this->assertNull(Auth::guard('web')->id(), 'Invalid logged in user id');
    }

    /** @test */
    public function it_increments_the_users_login_attempts_if_an_invalid_password_is_entered()
    {
        $user = $this->getUser('user@example.com');

        $this->assertDatabaseHas('users', [
            'id'             => $user->id,
            'login_attempts' => null,
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'not-the-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertNull(Auth::guard('web')->id(), 'Invalid logged in user id');

        $this->assertDatabaseHas('users', [
            'id'             => $user->id,
            'login_attempts' => 1,
        ]);
    }

    /** @test */
    public function it_resets_the_login_attempts_when_the_correct_password_is_entered()
    {
        $this->withoutExceptionHandling();
        $user = $this->getUser('user@example.com');

        $user->login_attempts = 2;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => 2,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect();

        $this->assertEquals($user->id, Auth::guard('web')->id(), 'Invalid logged in user id');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => null,
        ]);
    }

    /** @test */
    public function it_does_not_reset_the_login_attempts_if_the_user_is_already_locked_out_even_when_the_correct_password_is_entered()
    {
        $user = $this->getUser('user@example.com');

        $user->login_attempts = 5;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => 5,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect();

        $this->assertEquals($user->id, Auth::guard('web')->id(), 'Invalid logged in user id');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => 5,
        ]);
    }

    /** @test */
    public function it_shows_a_lock_out_message_if_there_are_too_many_login_attempts()
    {
        $this->withoutExceptionHandling();
        $user = $this->getUser('user@example.com');

        $user->login_attempts = 6;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => 6,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect();

        $response = $this->get($response->headers->get('Location'))
            ->assertRedirect('/verify');

        $this->get($response->headers->get('Location'))
            ->assertStatus(200)
            ->assertSee('Your account has been locked due to too many login attempts')
            ->assertSee('Contact our staff to reset your account password');

        $this->assertEquals($user->id, Auth::guard('web')->id(), 'Invalid logged in user id');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => 6,
        ]);
    }
}
