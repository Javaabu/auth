<?php

namespace Javaabu\Auth\Tests\Feature;

use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SecurityTest extends TestCase
{
    protected function confirmedSession(string $type = 'user'): array
    {
        return [RequirePassword::sessionKey($this->userType($type)) => time()];
    }

    #[Test]
    public function it_shows_the_security_page(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->withSession($this->confirmedSession())
            ->get('/settings/security')
            ->assertOk()
            ->assertSee('data-password-form', false)
            ->assertSee('data-two-factor', false)
            ->assertSee('data-passkeys', false);
    }

    #[Test]
    public function it_updates_the_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->put('/settings/password', [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(password_verify('new-password-123', $user->fresh()->password));
    }

    #[Test]
    public function it_needs_the_current_password(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->put('/settings/password', [
                'current_password' => 'wrong',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');
    }

    #[Test]
    public function users_who_must_change_their_password_are_sent_to_the_new_password_page(): void
    {
        $user = User::factory()->create(['require_password_update' => true]);

        $this->actingAs($user, 'web')
            ->get('/dashboard')
            ->assertRedirect('/update-password');

        $this->get('/update-password')->assertOk()->assertSee('data-auth-page="update-required-password"', false);

        $this->put('/update-password', [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect('/dashboard');

        $this->assertFalse($user->fresh()->requiresPasswordUpdate());
        $this->get('/dashboard')->assertOk();
    }
}
