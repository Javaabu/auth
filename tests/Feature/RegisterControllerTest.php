<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class RegisterControllerTest extends TestCase
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
    public function it_wont_display_the_user_registration_page()
    {
        $this->get('/register')
            ->assertStatus(404)
            ->assertDontSee('Register');
    }

    /** @test */
    public function it_wont_register_a_user()
    {
        $this->post('/register', [
            'name' => 'User',
            'email' => 'user@javaabu.com',
            'password' => 'Jv7528222',
            'password_confirmation' => 'Jv7528222',
        ])
            ->assertStatus(404);

        $this->assertDatabaseMissing('users', [
            'name' => 'User',
            'email' => 'user@javaabu.com',
        ]);
    }

    /** @test */
    /*public function it_can_display_the_user_registration_page()
    {
        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('Register');
    }*/

    /** @test */
    /*public function it_can_register_a_user()
    {
        $this->post('/register', [
            'name' => 'User',
            'email' => 'user@javaabu.com',
            'password' => 'Jv7528222',
            'password_confirmation' => 'Jv7528222'
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect('');

        $this->assertDatabaseHas('users', [
            'name' => 'User',
            'email' => 'user@javaabu.com',
            'status' => UserStatuses::UNVERIFIED,
        ]);

        $user = User::first();

        Notification::assertSentTo(
            [$user],
            EmailVerification::class
        );
    }*/

    /** @test */
    /*public function it_can_validate_the_registration_inputs()
    {
        $this->post('/register', [
            'name' => 'User',
        ])
            ->assertSessionHasErrors('password', 'email');

        $this->assertDatabaseMissing('users', [
            'name' => 'User',
        ]);

        $this->post('/register', [
            'name' => '',
            'email' => '',
            'password' => 'Jv7528222',
            'password_confirmation' => 'Jv7528222',
        ])
            ->assertSessionHasErrors('name', 'email');

        $this->assertDatabaseMissing('users', [
            'name' => '',
        ]);
    }*/
}
