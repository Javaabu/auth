<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;

class ResetPasswordControllerTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
        $this->seedDefaultUsers();
    }

    /**
     * Get the token for the user
     *
     * @param  null  $broker
     */
    protected function getResetToken($user, $broker = null): string
    {
        return app(PasswordBrokerManager::class)
            ->broker($broker)
            ->createToken($user);
    }

    #[Test]
    public function it_can_display_the_reset_password_page()
    {
        $user = $this->getUser('user@example.com');
        $token = $this->getResetToken($user);

        $this->get("/password/reset/{$token}")
            ->assertStatus(200)
            ->assertSee('Reset Password');
    }

    #[Test]
    public function it_resets_the_login_attempts_when_the_password_is_reset()
    {
        $this->withoutExceptionHandling();

        $user = $this->getUser('user@example.com');
        $user->login_attempts = 6;
        $user->save();

        $token = $this->getResetToken($user, 'users');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => 6,
        ]);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertRedirect()
            ->assertSessionMissing('errors');

        $user = $user->fresh();
        $this->assertTrue(Hash::check('abc12345', $user->password), 'Invalid password');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_attempts' => null,
        ]);
    }

    #[Test]
    public function it_can_reset_the_password()
    {
        $this->withoutExceptionHandling();

        $user = $this->getUser('user@example.com');
        $token = $this->getResetToken($user, 'users');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->post('/password/reset', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect();

        $user = $user->fresh();
        $this->assertTrue(Hash::check('abc12345', $user->password), 'Invalid password');
    }

    #[Test]
    public function it_records_the_password_reset_event()
    {
        $this->withoutExceptionHandling();

        $now = '2024-09-08 12:56:00';

        $this->travelTo($now);

        $user = $this->getUser('user@example.com');
        $token = $this->getResetToken($user, 'users');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->post('/password/reset', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect();

        $user = $user->fresh();
        $this->assertTrue(Hash::check('abc12345', $user->password), 'Invalid password');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'password_reset',
            'causer_type' => $user->getMorphClass(),
            'causer_id' => $user->id,
            'created_at' => $now,
        ]);
    }

    #[Test]
    public function it_does_not_allow_the_password_to_reset_using_an_invalid_token()
    {
        $user = $this->getUser('user@example.com');
        $token = $this->getResetToken($user);

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->post('/password/reset', [
            'token' => 'invalid_token',
            'email' => 'user@example.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertSessionHasErrors('email');

        $user = $user->fresh();
        $this->assertFalse(Hash::check('abc12345', $user->password), 'Invalid password');
    }
}
