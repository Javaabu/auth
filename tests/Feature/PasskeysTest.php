<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Testing\TestResponse;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Passkeys\Passkey;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\Support\SoftwareAuthenticator;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;

class PasskeysTest extends TestCase
{
    protected function confirmed(string $type = 'user'): array
    {
        return [RequirePassword::sessionKey($this->userType($type)) => time()];
    }

    /**
     * Add a passkey through the security page endpoints
     */
    protected function addPasskey(SoftwareAuthenticator $authenticator, string $prefix = '', string $name = 'ލެޕްޓޮޕް'): void
    {
        $options = $this->getJson("{$prefix}/settings/passkeys/options")->assertOk()->json('options');

        $this->postJson("{$prefix}/settings/passkeys", [
            'name' => $name,
            'credential' => $authenticator->register($options),
        ])->assertCreated();
    }

    protected function logInWithPasskey(SoftwareAuthenticator $authenticator, string $prefix = ''): TestResponse
    {
        $options = $this->getJson("{$prefix}/passkeys/login/options")->assertOk()->json('options');

        return $this->postJson("{$prefix}/passkeys/login", [
            'credential' => $authenticator->assert($options),
        ]);
    }

    #[Test]
    public function it_adds_a_passkey_and_logs_in_with_it(): void
    {
        $user = User::factory()->create();
        $authenticator = new SoftwareAuthenticator;

        $this->actingAs($user, 'web')->withSession($this->confirmed());

        $this->addPasskey($authenticator);

        $this->assertDatabaseHas('passkeys', [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
            'name' => 'ލެޕްޓޮޕް',
            'credential_id' => $authenticator->credentialId(),
        ]);

        $this->post('/logout');
        $this->assertGuest('web');

        $this->logInWithPasskey($authenticator)
            ->assertOk()
            ->assertJson(['two_factor' => false, 'redirect' => url('/dashboard')]);

        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function a_passkey_skips_the_two_factor_code(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $authenticator = new SoftwareAuthenticator;

        $this->actingAs($user, 'web')->withSession($this->confirmed());
        $this->addPasskey($authenticator);
        $this->post('/logout');

        $this->logInWithPasskey($authenticator)->assertJson(['two_factor' => false]);

        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function a_users_passkey_cannot_log_in_as_an_admin(): void
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $authenticator = new SoftwareAuthenticator;

        $this->actingAs($user, 'web')->withSession($this->confirmed());
        $this->addPasskey($authenticator);
        $this->post('/logout');

        $this->logInWithPasskey($authenticator, '/admin')->assertUnprocessable();

        $this->assertGuest('web_admin');
        $this->assertGuest('web');
    }

    #[Test]
    public function the_same_id_on_two_types_keeps_separate_passkeys(): void
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create(['id' => $user->id]);

        $this->actingAs($user, 'web')->withSession($this->confirmed());
        $this->addPasskey(new SoftwareAuthenticator, name: 'user key');

        $this->actingAs($admin, 'web_admin')->withSession($this->confirmed('admin'));
        $this->addPasskey(new SoftwareAuthenticator, '/admin', 'admin key');

        $this->assertEquals(['user key'], $user->passkeys()->pluck('name')->all());
        $this->assertEquals(['admin key'], $admin->passkeys()->pluck('name')->all());
    }

    #[Test]
    public function it_confirms_the_password_with_a_passkey(): void
    {
        $user = User::factory()->create();
        $authenticator = new SoftwareAuthenticator;

        $this->actingAs($user, 'web')->withSession($this->confirmed());
        $this->addPasskey($authenticator);

        $this->flushSession();
        $this->actingAs($user, 'web');

        $this->get('/settings/security')->assertRedirect('/confirm-password');
        $this->get('/confirm-password')->assertSee('data-passkey-confirm', false);

        $options = $this->getJson('/passkeys/confirm/options')->assertOk()->json('options');

        $this->postJson('/passkeys/confirm', ['credential' => $authenticator->assert($options)])
            ->assertOk()
            ->assertJson(['redirect' => url('/settings/security')]);

        $this->get('/settings/security')->assertOk();
    }

    #[Test]
    public function it_rejects_a_forged_signature(): void
    {
        $user = User::factory()->create();
        $authenticator = new SoftwareAuthenticator;

        $this->actingAs($user, 'web')->withSession($this->confirmed());
        $this->addPasskey($authenticator);
        $this->post('/logout');

        $options = $this->getJson('/passkeys/login/options')->json('options');
        $credential = $authenticator->assert($options);
        $credential['response']['signature'] = (new SoftwareAuthenticator)->assert($options)['response']['signature'];

        $this->postJson('/passkeys/login', ['credential' => $credential])->assertUnprocessable();

        $this->assertGuest('web');
    }

    #[Test]
    public function it_removes_only_the_users_own_passkeys(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other, 'web')->withSession($this->confirmed());
        $this->addPasskey(new SoftwareAuthenticator);
        $passkey = Passkey::firstOrFail();

        $this->actingAs($user, 'web')->withSession($this->confirmed())
            ->deleteJson("/settings/passkeys/{$passkey->id}")
            ->assertNotFound();

        $this->actingAs($other, 'web')
            ->deleteJson("/settings/passkeys/{$passkey->id}")
            ->assertOk();

        $this->assertDatabaseCount('passkeys', 0);
    }

    #[Test]
    public function the_security_page_lists_passkeys(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->withSession($this->confirmed());
        $this->addPasskey(new SoftwareAuthenticator);

        $this->get('/settings/security')
            ->assertOk()
            ->assertSee('ލެޕްޓޮޕް')
            ->assertSee('data-passkey-list', false);
    }

    #[Test]
    public function types_without_passkeys_have_no_passkey_routes(): void
    {
        UserTypes::get('user')->disable('passkeys');

        $this->assertTrue(UserTypes::get('admin')->hasRoute('passkey.login'));
    }
}
