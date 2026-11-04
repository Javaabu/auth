<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RouteOptionsTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::authRoutes('user', ['except' => ['password_reset', 'passkeys']]);
        Route::authRoutes('admin', ['prefix' => 'staff', 'account_prefix' => 'account', 'middleware' => ['web', 'throttle:60,1']]);
    }

    #[Test]
    public function it_leaves_out_features_listed_in_except(): void
    {
        $this->assertFalse(Route::has('password.request'));
        $this->assertFalse(Route::has('passkey.login'));
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('admin.passkey.login'));
    }

    #[Test]
    public function it_uses_the_prefixes_and_middleware(): void
    {
        $this->assertEquals(url('/staff/login'), route('admin.login'));
        $this->assertEquals(url('/staff/account/profile'), route('admin.profile.edit'));
        $this->assertContains('throttle:60,1', Route::getRoutes()->getByName('admin.login')->gatherMiddleware());
    }
}
