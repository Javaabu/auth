<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LoginTest extends TestCase
{
    #[Test]
    public function it_shows_the_login_page_of_each_user_type(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-auth-page="login"', false)
            ->assertSee(route('login.store'), false)
            ->assertSee(route('register'), false);

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(route('admin.login.store'), false)
            ->assertDontSee(route('register'), false);
    }

    #[Test]
    public function it_logs_a_user_in_on_their_own_guard(): void
    {
        $user = User::factory()->create(['email' => 'ahmed@example.com']);

        Event::fake([Login::class]);

        $this->post('/login', ['email' => 'Ahmed@Example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('web_admin');

        Event::assertDispatched(Login::class, fn (Login $event) => $event->guard === 'web');
    }

    #[Test]
    public function it_logs_an_admin_in_on_the_admin_guard(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'web_admin');
        $this->assertGuest('web');
    }

    #[Test]
    public function a_user_cannot_log_in_on_the_admin_login_page(): void
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->post('/admin/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
        $this->assertGuest('web_admin');
    }

    #[Test]
    public function it_rejects_a_wrong_password_and_fires_the_failed_event(): void
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        Event::fake([Failed::class]);

        $this->from('/login')
            ->post('/login', ['email' => 'ahmed@example.com', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

        $this->assertGuest('web');

        Event::assertDispatched(Failed::class);
    }

    #[Test]
    public function it_rate_limits_login_attempts(): void
    {
        config()->set('javaabu-auth.max_login_attempts', null);

        User::factory()->create(['email' => 'ahmed@example.com']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    #[Test]
    public function it_locks_the_account_after_too_many_failed_logins(): void
    {
        config()->set('javaabu-auth.max_login_attempts', 3);
        config()->set('javaabu-auth.rate_limits.login', 100);

        $user = User::factory()->create(['email' => 'ahmed@example.com']);

        foreach (range(1, 3) as $attempt) {
            $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'wrong']);
        }

        $this->assertTrue($user->fresh()->isLockedOut());

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    #[Test]
    public function it_resets_failed_logins_after_a_successful_login(): void
    {
        $user = User::factory()->create(['email' => 'ahmed@example.com', 'login_attempts' => 2]);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertNull($user->fresh()->login_attempts);
    }

    #[Test]
    public function it_sends_signed_in_users_away_from_the_login_page(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/login')
            ->assertRedirect('/dashboard');

        // signed in as a user, but not as an admin
        $this->get('/admin/login')->assertOk();
    }

    #[Test]
    public function it_returns_json_for_json_logins(): void
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->postJson('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJson(['two_factor' => false, 'redirect' => url('/dashboard')]);
    }

    #[Test]
    public function it_logs_out_only_the_guard_of_the_route(): void
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $this->actingAs($user, 'web')->actingAs($admin, 'web_admin');

        $this->post('/admin/logout')->assertRedirect('/admin/login');

        $this->assertGuest('web_admin');
        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function it_redirects_guests_to_the_login_page_of_the_type(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    #[Test]
    public function it_sends_pending_and_banned_users_to_the_inactive_page(): void
    {
        $user = User::factory()->pending()->create();

        $this->actingAs($user, 'web')
            ->get('/dashboard')
            ->assertRedirect(route('account.inactive'));

        $this->get(route('account.inactive'))
            ->assertOk()
            ->assertSee('Your account needs to be approved before you can access it.');
    }

    #[Test]
    public function it_sends_users_with_unverified_emails_to_the_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user, 'web')
            ->get('/dashboard')
            ->assertRedirect(route('verification.notice'));
    }
}
