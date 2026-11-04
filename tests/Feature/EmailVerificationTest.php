<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Javaabu\Auth\Notifications\VerifyEmail;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EmailVerificationTest extends TestCase
{
    #[Test]
    public function it_shows_the_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user, 'web')
            ->get('/verify-email')
            ->assertOk()
            ->assertSee('data-auth-page="verify-email"', false);
    }

    #[Test]
    public function it_verifies_the_email_from_the_signed_link(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user, 'web')->get($url)->assertRedirect('/dashboard');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        Event::assertDispatched(Verified::class);
    }

    #[Test]
    public function it_rejects_a_link_for_someone_else(): void
    {
        $user = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $other->id,
            'hash' => sha1($other->email),
        ]);

        $this->actingAs($user, 'web')->get($url)->assertForbidden();
    }

    #[Test]
    public function it_resends_the_link_to_the_type_route(): void
    {
        Notification::fake();

        $admin = Admin::factory()->unverified()->create();

        $this->actingAs($admin, 'web_admin')
            ->post('/admin/email/verification-notification')
            ->assertSessionHas('status');

        Notification::assertSentTo($admin, VerifyEmail::class, function (VerifyEmail $notification) use ($admin) {
            return str_contains($notification->toMail($admin)->actionUrl, '/admin/verify-email/');
        });
    }
}
