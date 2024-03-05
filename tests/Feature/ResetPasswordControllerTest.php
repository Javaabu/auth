<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

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

    /** @test */
    public function it_can_display_the_reset_password_page()
    {
        $user = $this->getUser('user@example.com');
        $token = $this->getResetToken($user);

        $this->get("/password/reset/{$token}")
            ->assertStatus(200)
            ->assertSee('Reset Password');
    }

    /** @test */
    public function it_can_reset_the_password()
    {
        $user = $this->getUser('user@example.com');
        $token = $this->getResetToken($user, 'users');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->post('/password/reset', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertSessionMissing('errors');

        $user = $user->fresh();
        $this->assertTrue(Hash::check('abc12345', $user->password), 'Invalid password');
    }

    /** @test */
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
