<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Javaabu\Auth\Events\TwoFactorAuthenticationChallenged;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;

class TwoFactorTest extends TestCase
{
    protected const string SECRET = 'JBSWY3DPEHPK3PXP';

    protected function code(string $secret = self::SECRET): string
    {
        return app(TwoFactorProvider::class)->currentCode($secret);
    }

    protected function confirmed(string $type = 'user'): array
    {
        return [RequirePassword::sessionKey($this->userType($type)) => time()];
    }

    #[Test]
    public function it_turns_two_factor_on_and_confirms_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->withSession($this->confirmed())
            ->post('/settings/two-factor')
            ->assertSessionHas('two_factor_status', 'two-factor-authentication-enabled');

        $user->refresh();

        $this->assertTrue($user->hasPendingTwoFactorAuthentication());
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
        $this->assertCount(8, $user->recoveryCodes());

        $this->get('/settings/security')
            ->assertOk()
            ->assertSee('data-two-factor-qr-code', false);

        $this->post('/settings/two-factor/confirm', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->post('/settings/two-factor/confirm', ['code' => $this->code($user->twoFactorSecret())])
            ->assertSessionHasNoErrors();

        $this->assertTrue($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    #[Test]
    public function managing_two_factor_needs_a_confirmed_password(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->post('/settings/two-factor')
            ->assertRedirect('/confirm-password');
    }

    #[Test]
    public function it_asks_for_a_code_after_the_password(): void
    {
        Event::fake([TwoFactorAuthenticationChallenged::class]);

        $user = User::factory()->withTwoFactor(self::SECRET)->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertRedirect('/two-factor-challenge');

        $this->assertGuest('web');
        Event::assertDispatched(TwoFactorAuthenticationChallenged::class);

        $this->get('/two-factor-challenge')->assertOk()->assertSee('data-two-factor-challenge', false);

        $this->post('/two-factor-challenge', ['code' => '123456'])
            ->assertSessionHasErrors('code');

        $this->assertGuest('web');

        $this->post('/two-factor-challenge', ['code' => $this->code()])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function a_code_can_only_be_used_once(): void
    {
        $provider = app(TwoFactorProvider::class);
        $code = $this->code();

        $this->assertTrue($provider->verify(self::SECRET, $code));
        $this->assertFalse($provider->verify(self::SECRET, $code));
    }

    #[Test]
    public function it_accepts_a_recovery_code_once(): void
    {
        $user = User::factory()->withTwoFactor()->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password']);

        $this->post('/two-factor-challenge', ['recovery_code' => 'recovery-code-1'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotContains('recovery-code-1', $user->fresh()->recoveryCodes());
        $this->assertContains('recovery-code-2', $user->fresh()->recoveryCodes());
    }

    #[Test]
    public function a_challenge_of_one_type_cannot_be_finished_on_another(): void
    {
        User::factory()->withTwoFactor()->create(['email' => 'ahmed@example.com']);
        Admin::factory()->create(['id' => 1]);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password']);

        $this->get('/admin/two-factor-challenge')->assertRedirect('/admin/login');

        $this->post('/admin/two-factor-challenge', ['code' => $this->code()])
            ->assertSessionHasErrors('code');

        $this->assertGuest('web_admin');
    }

    #[Test]
    public function it_rate_limits_codes(): void
    {
        User::factory()->withTwoFactor()->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/two-factor-challenge', ['code' => '000000']);
        }

        $this->post('/two-factor-challenge', ['code' => $this->code()])
            ->assertSessionHasErrors('code');

        $this->assertGuest('web');
    }

    #[Test]
    public function types_with_two_factor_off_skip_the_challenge(): void
    {
        UserTypes::get('user')->twoFactor('off');

        User::factory()->withTwoFactor()->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function required_two_factor_sends_users_to_set_it_up(): void
    {
        UserTypes::get('admin')->twoFactor('required');

        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'web_admin')->withSession($this->confirmed('admin'))
            ->get('/admin')
            ->assertRedirect('/admin/settings/security');

        $this->get('/admin/settings/security')->assertOk();

        $this->actingAs(Admin::factory()->withTwoFactor()->create(), 'web_admin')
            ->delete('/admin/settings/two-factor')
            ->assertForbidden();
    }

    #[Test]
    public function it_turns_two_factor_off(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($user, 'web')->withSession($this->confirmed())
            ->delete('/settings/two-factor')
            ->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    #[Test]
    public function it_regenerates_recovery_codes_and_shows_them_once(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($user, 'web')->withSession($this->confirmed())
            ->post('/settings/two-factor/recovery-codes');

        $codes = $user->fresh()->recoveryCodes();

        $this->assertNotContains('recovery-code-1', $codes);

        $this->getJson('/settings/two-factor/recovery-codes')->assertExactJson($codes);
    }

    #[Test]
    public function it_returns_the_qr_code_and_secret_as_json(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($user, 'web')->withSession($this->confirmed())
            ->getJson('/settings/two-factor/qr-code')
            ->assertOk()
            ->assertJsonStructure(['svg', 'url']);

        $this->getJson('/settings/two-factor/secret-key')
            ->assertExactJson(['secret_key' => self::SECRET]);
    }
}
