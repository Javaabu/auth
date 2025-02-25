<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Mail\EmailUpdated;
use Javaabu\Auth\Notifications\VerifyEmail;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class VerificationControllerTest extends TestCase
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
    public function it_redirects_to_email_verification_page_if_the_user_is_not_verified()
    {
        $user = User::factory()
            ->unverified()
            ->pending()
            ->create([
                'email' => 'verification-test-user@example.com',
            ]);

        $this->actingAs($user);

        $this->get('/')
            ->assertRedirect('/verify');

        $this->get('/verify')
            ->assertSee('Resend Verification');
    }

    #[Test]
    public function it_can_resend_the_email_verification()
    {
        $user = User::factory()
            ->unverified()
            ->pending()
            ->create([
                'email' => 'verification-test-user@example.com',
            ]);

        $this->actingAs($user);

        $this->post('/verify/email/resend')
            ->assertSessionMissing('errors');

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
    }

    #[Test]
    public function it_can_verify_the_email()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()
            ->unverified()
            ->pending()
            ->create([
                'email' => 'verification-test-user@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatuses::PENDING,
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            $user->getRouteForEmailVerification(),
            \Illuminate\Support\Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->get($url)
            ->assertStatus(200)
            ->assertSessionMissing('errors')
            ->assertSee('Your email has been verified successfully');

        $this->assertNotNull($user->email_verified_at);
    }

    #[Test]
    public function it_can_verify_the_new_email()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()
            ->active()
            ->create([
                'email' => 'old@example.com',
                'new_email' => 'new-email@example.com'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'old@example.com',
            'new_email' => 'new-email@example.com',
        ]);

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            $user->getRouteForEmailVerification(),
            \Illuminate\Support\Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->get($url)
            ->assertStatus(200)
            ->assertSessionMissing('errors')
            ->assertSee('Your email has been updated successfully to new-email@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new-email@example.com',
            'new_email' => null,
        ]);

        Mail::assertSent(EmailUpdated::class, function (EmailUpdated $mail) {
            return $mail->hasTo('old@example.com');
        });
    }

    #[Test]
    public function it_does_not_allow_the_email_to_be_verified_using_an_invalid_token()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()
            ->unverified()
            ->pending()
            ->create([
                'email' => 'verification-test-user@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatuses::PENDING,
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            $user->getRouteForEmailVerification(),
            \Illuminate\Support\Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey() + 1,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $thing = $this->get($url)
            ->assertStatus(200)
            ->assertSessionMissing('errors')
            ->assertSee('Verification token is invalid');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatuses::PENDING,
            'email_verified_at' => null,
        ]);
    }
}
