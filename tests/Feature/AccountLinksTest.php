<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Support\PageData;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;

class AccountLinksTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        $this->registerAuthRoutes();

        // A package's account page, registered for the user type only
        Route::middleware(['web', 'auth.type:user'])
            ->get('settings/mobile-number', fn () => view('mobile-number'))
            ->name('mobile-number');
    }

    protected function confirmed(string $type = 'user'): array
    {
        return [RequirePassword::sessionKey($this->userType($type)) => time()];
    }

    /** @return array<int, string> */
    protected function keys(string $type): array
    {
        return array_column(app(PageData::class)->accountLinks(UserTypes::get($type)), 'key');
    }

    #[Test]
    public function the_navigation_has_profile_and_security_by_default(): void
    {
        $this->assertEquals(['profile', 'security'], $this->keys('user'));
        $this->assertEquals(['profile', 'security'], $this->keys('admin'));
    }

    #[Test]
    public function a_link_added_for_every_type_shows_where_its_route_exists(): void
    {
        UserTypes::accountLink('mobile-number', 'Mobile number');

        $this->assertEquals(['profile', 'security', 'mobile-number'], $this->keys('user'));
        $this->assertEquals(['profile', 'security'], $this->keys('admin'));
    }

    #[Test]
    public function a_link_can_be_added_to_one_type_with_its_own_route_and_position(): void
    {
        UserTypes::get('user')->accountLink('phone', fn (UserType $type) => "ފޯނު ({$type->name()})", route: 'mobile-number', position: 15);

        $links = app(PageData::class)->accountLinks(UserTypes::get('user'));

        $this->assertEquals(['profile', 'phone', 'security'], array_column($links, 'key'));
        $this->assertEquals('ފޯނު (user)', $links[1]['label']);
        $this->assertEquals(url('/settings/mobile-number'), $links[1]['url']);
    }

    #[Test]
    public function a_type_can_drop_a_link(): void
    {
        UserTypes::accountLink('mobile-number', 'Mobile number');
        UserTypes::get('user')->withoutAccountLink('mobile-number')->withoutAccountLink('security');

        $this->assertEquals(['profile'], $this->keys('user'));
    }

    #[Test]
    public function the_labels_are_translated(): void
    {
        $this->app->setLocale('dv');

        $links = app(PageData::class)->accountLinks(UserTypes::get('user'));

        $this->assertEquals(['ޕްރޮފައިލް', 'ސެކިއުރިޓީ'], array_column($links, 'label'));
    }

    #[Test]
    public function the_blade_navigation_shows_the_links_and_marks_the_current_page(): void
    {
        config()->set('javaabu-auth.layouts.account', 'auth::layouts.account');

        UserTypes::accountLink('mobile-number', 'Mobile number');

        $this->actingAs(User::factory()->create(), 'web')->withSession($this->confirmed())
            ->get('/settings/security')
            ->assertOk()
            ->assertSee('data-account-link="mobile-number"', false)
            ->assertSee(url('/settings/mobile-number'), false)
            ->assertSee('aria-current="page"', false);
    }

    #[Test]
    public function the_navigation_works_on_a_package_page_that_did_not_come_from_auth(): void
    {
        UserTypes::accountLink('mobile-number', 'Mobile number');

        $response = $this->actingAs(User::factory()->create(), 'web')
            ->get('/settings/mobile-number')
            ->assertOk()
            ->assertSee('data-account-link="profile"', false)
            ->assertSee(route('logout'), false);

        $this->assertEquals(1, substr_count($response->getContent(), 'aria-current="page"'));
        $this->assertMatchesRegularExpression('/data-account-link="mobile-number"[^>]*aria-current="page"|aria-current="page"[^>]*data-account-link="mobile-number"/', $response->getContent());
    }

    #[Test]
    public function admins_do_not_get_links_to_pages_they_do_not_have(): void
    {
        config()->set('javaabu-auth.layouts.account', 'auth::layouts.account');

        UserTypes::accountLink('mobile-number', 'Mobile number');

        $this->actingAs(Admin::factory()->create(), 'web_admin')->withSession($this->confirmed('admin'))
            ->get('/admin/settings/profile')
            ->assertOk()
            ->assertSee('data-account-link="security"', false)
            ->assertDontSee('data-account-link="mobile-number"', false);
    }

    #[Test]
    public function flushing_the_registry_forgets_the_links(): void
    {
        UserTypes::accountLink('mobile-number', 'Mobile number');

        app(UserTypeRegistry::class)->flush();

        $this->assertEquals([], UserTypes::accountLinks());
    }
}
