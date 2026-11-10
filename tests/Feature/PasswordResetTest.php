<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Javaabu\Auth\Notifications\ResetPassword;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PasswordResetTest extends TestCase
{
    #[Test]
    public function it_emails_a_reset_link_to_the_type_reset_page(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $this->post('/admin/forgot-password', ['email' => 'admin@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($admin, ResetPassword::class, function (ResetPassword $notification) use ($admin) {
            $url = $notification->toMail($admin)->actionUrl;

            return str_starts_with($url, url('/admin/reset-password/'));
        });
    }

    #[Test]
    public function it_answers_the_same_for_unknown_emails(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_resets_the_password_and_unlocks_the_account(): void
    {
        $user = User::factory()->create(['email' => 'ahmed@example.com', 'login_attempts' => 10, 'require_password_update' => true]);
        $token = Password::broker('users')->createToken($user);

        $this->get("/reset-password/{$token}?email=ahmed@example.com")
            ->assertOk()
            ->assertSee('data-auth-page="reset-password"', false);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'ahmed@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect('/login');

        $user->refresh();

        $this->assertTrue(password_verify('new-password-123', $user->password));
        $this->assertNull($user->login_attempts);
        $this->assertFalse($user->requiresPasswordUpdate());
    }

    #[Test]
    public function a_token_of_one_type_does_not_reset_another_type(): void
    {
        $user = User::factory()->create(['email' => 'same@example.com']);
        Admin::factory()->create(['email' => 'same@example.com']);
        $token = Password::broker('users')->createToken($user);

        $this->post('/admin/reset-password', [
            'token' => $token,
            'email' => 'same@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('email');
    }
}
