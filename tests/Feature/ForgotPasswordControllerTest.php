<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\Auth\Notifications\ResetPassword;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

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
