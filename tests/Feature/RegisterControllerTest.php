<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Notifications\VerifyEmail;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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

    #[Test]
    public function it_can_display_the_user_registration_page()
    {
        $this->withoutExceptionHandling();

        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('Register');
    }

    #[Test]
    public function it_can_register_a_user()
    {
        $this->withoutExceptionHandling();

        $this->post('/register', [
            'name' => 'User',
            'email' => 'user@javaabu.com',
            'password' => 'TestPass123',
            'password_confirmation' => 'TestPass123',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect('');

        $user = User::whereEmail('user@javaabu.com')->first();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'User',
            'email' => 'user@javaabu.com',
            'email_verified_at' => null,
            'status' => UserStatuses::PENDING,
        ]);

        $this->assertEquals($user->id, Auth::guard('web')->id());

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );

        Notification::assertSentToTimes(
            $user,
            VerifyEmail::class,
            1);
    }

    #[Test]
    public function it_can_validate_the_registration_inputs()
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
            'password' => 'TestPass123',
            'password_confirmation' => 'TestPass123',
        ])
            ->assertSessionHasErrors('name', 'email');

        $this->assertDatabaseMissing('users', [
            'name' => '',
        ]);
    }
}
