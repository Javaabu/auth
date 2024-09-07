<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\Auth\Notifications\ResetPassword;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class ForgotPasswordControllerTest extends TestCase
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
    public function it_can_display_the_forgot_password_page()
    {
        $this->get('/password/reset')
            ->assertStatus(200)
            ->assertSee('Forgot Password');
    }

    /** @test */
    public function it_can_send_the_forgot_password_link()
    {
        $user = $this->getUser('user@example.com');

        $this->post('/password/email', [
            'email' => 'user@example.com',
        ])
            ->assertSessionMissing('errors');

        Notification::assertSentToTimes(
            $user,
            ResetPassword::class,
        );
    }

    /** @test */
    public function it_records_the_password_reset_link_sent_event()
    {
        $this->withoutExceptionHandling();

        $now = '2024-09-08 12:56:00';

        $this->travelTo($now);

        $user = $this->getUser('user@example.com');

        $this->post('/password/email', [
            'email' => 'user@example.com',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect();

        Notification::assertSentToTimes(
            $user,
            ResetPassword::class,
        );

        /** @var Activity $log */
        $log = Activity::latest('id')->first();

        $this->assertDatabaseHas('activity_log', [
            'id' => $log->id,
            'description' => 'password_reset_link_sent',
            'causer_type' => $user->getMorphClass(),
            'causer_id' => $user->id,
            'created_at' => $now,
        ]);
    }

    /** @test */
    public function it_can_validate_the_forgot_password_inputs()
    {
        $user = $this->getUser('user@example.com');

        $this->post('/password/email', [
            'email' => 'another-user@example.com',
        ])
            ->assertSessionHasErrors('email');

        Notification::assertNotSentTo(
            [$user],
            ResetPassword::class
        );

        $this->post('/password/email', [
            'email' => '',
        ])
            ->assertSessionHasErrors('email');

        Notification::assertNotSentTo(
            [$user],
            ResetPassword::class
        );
    }
}
