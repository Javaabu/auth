<?php

namespace Javaabu\Auth\Tests\Http;

use Inertia\ServiceProvider as InertiaServiceProvider;
use Inertia\Testing\AssertableInertia;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Support\Strings;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('optional')]
class InertiaPagesTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), InertiaServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('javaabu-auth.views', 'inertia');
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
        $app['config']->set('inertia.root_view', 'app');
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function guestPages(): array
    {
        return [
            'login' => ['/login', 'auth::Login'],
            'register' => ['/register', 'auth::Register'],
            'forgot password' => ['/forgot-password', 'auth::ForgotPassword'],
            'reset password' => ['/reset-password/token?email=a@example.com', 'auth::ResetPassword'],
            'admin login' => ['/admin/login', 'auth::Login'],
        ];
    }

    #[Test]
    #[DataProvider('guestPages')]
    public function it_renders_the_guest_pages_as_inertia_pages(string $url, string $component): void
    {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component($component, false)
                ->has('auth_type.name')
                ->where('urls', fn ($urls) => isset($urls['login.store'], $urls['home']))
                ->has('strings.log_in'));
    }

    #[Test]
    public function every_page_file_exists(): void
    {
        $pages = ['Login', 'Register', 'ForgotPassword', 'ResetPassword', 'VerifyEmail', 'ConfirmPassword', 'TwoFactorChallenge', 'AccountInactive', 'UpdateRequiredPassword', 'Settings/Profile', 'Settings/Security'];

        foreach ($pages as $page) {
            $this->assertFileExists(__DIR__ . "/../../resources/js/Pages/{$page}.tsx");
        }
    }

    #[Test]
    public function the_login_page_props_match_the_type(): void
    {
        $this->get('/admin/login')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth_type.name', 'admin')
                ->where('urls', fn ($urls) => $urls['login.store'] === url('/admin/login'))
                ->where('can_register', false)
                ->where('can_use_passkeys', true)
                ->etc());
    }

    #[Test]
    public function the_strings_are_translated(): void
    {
        $this->app->setLocale('dv');

        $this->get('/login')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('strings.log_in', __('Log in'))
                ->has('strings', count(Strings::ALL))
                ->etc());

        $this->assertNotEquals('Log in', __('Log in'));
    }

    #[Test]
    public function it_renders_the_account_pages(): void
    {
        $user = User::factory()->withTwoFactor()->create(['name' => 'ޒިޔާ']);
        $session = [RequirePassword::sessionKey(UserTypes::get('user')) => time()];

        $this->actingAs($user, 'web')->withSession($session)
            ->get('/settings/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('auth::Settings/Profile', false)
                ->where('profile.name', 'ޒިޔާ')
                ->where('can_delete_account', true)
                ->etc());

        $this->get('/settings/security')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('auth::Settings/Security', false)
                ->where('two_factor.enabled', true)
                ->where('account_links.1.key', 'security')
                ->where('account_links.1.active', true)
                ->where('passkeys', [])
                ->where('sessions', null)
                ->where('api_tokens', null)
                ->etc());
    }

    #[Test]
    public function a_type_can_use_blade_while_the_default_is_inertia(): void
    {
        UserTypes::get('admin')->views('blade');

        $this->get('/admin/login')->assertOk()->assertSee('data-auth-page="login"', false);
        $this->get('/login')->assertInertia(fn (AssertableInertia $page) => $page->component('auth::Login', false));
    }

    #[Test]
    public function signed_in_pages_render_for_admins(): void
    {
        $this->actingAs(Admin::factory()->unverified()->create(), 'web_admin')
            ->get('/admin/verify-email')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('auth::VerifyEmail', false)
                ->has('email')
                ->where('urls', fn ($urls) => $urls['verification.send'] === url('/admin/email/verification-notification'))
                ->etc());
    }
}
