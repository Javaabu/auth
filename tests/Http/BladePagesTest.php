<?php

namespace Javaabu\Auth\Tests\Http;

use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserTypes;
use Javaabu\Manifest\Facades\Manifest;
use PHPUnit\Framework\Attributes\Test;

class BladePagesTest extends TestCase
{
    #[Test]
    public function the_pages_render_in_dhivehi(): void
    {
        $this->app->setLocale('dv');

        $this->get('/login')
            ->assertOk()
            ->assertSee('ތިބާގެ އެކައުންޓަށް ލޮގިން ކުރޭ')
            ->assertSee('dir="ltr"', false);

        $this->get('/register')->assertOk()->assertSee('އެކައުންޓެއް ހަދާ');
        $this->get('/forgot-password')->assertOk()->assertSee('ޕާސްވޯޑް ހަނދާން ނެތީތޯ؟');
    }

    #[Test]
    public function the_validation_messages_are_in_dhivehi(): void
    {
        $this->app->setLocale('dv');

        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->from('/login')
            ->post('/login', ['email' => 'ahmed@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'މި މަޢުލޫމާތާ ގުޅޭ އެކައުންޓެއް ނުފެނުނު.']);
    }

    #[Test]
    public function the_package_layouts_render(): void
    {
        config()->set('javaabu-auth.layouts', ['auth' => 'auth::layouts.auth', 'account' => 'auth::layouts.account']);

        $this->app->setLocale('dv');

        $this->get('/login')
            ->assertOk()
            ->assertSee('<html lang="dv" dir="rtl">', false);

        $session = [RequirePassword::sessionKey(UserTypes::get('user')) => time()];

        $this->actingAs(User::factory()->create(), 'web')->withSession($session)
            ->get('/settings/security')
            ->assertOk()
            ->assertSee('data-account-nav', false)
            ->assertSee(route('logout'), false);
    }

    #[Test]
    public function the_pages_load_their_scripts_through_manifest(): void
    {
        $session = [RequirePassword::sessionKey(UserTypes::get('user')) => time()];

        $this->get('/login')->assertOk();

        Manifest::assertAssetRequired('auth::passkeys');

        $this->actingAs(User::factory()->create(), 'web')->withSession($session)
            ->get('/settings/profile')
            ->assertOk();

        Manifest::assertAssetRequired('auth::auth');
    }
}
