<?php

namespace Javaabu\Auth\Tests\Unit;

use InvalidArgumentException;
use Javaabu\Auth\Enums\TwoFactorModes;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;

class UserTypeTest extends TestCase
{
    #[Test]
    public function it_has_defaults_from_its_name(): void
    {
        $type = new UserType('vendor');

        $this->assertEquals('vendor', $type->guardName());
        $this->assertEquals('vendors', $type->passwordBrokerName());
        $this->assertEquals('vendor.login', $type->routeNameFor('login'));
        $this->assertEquals(TwoFactorModes::OPTIONAL, $type->twoFactorMode());
        $this->assertTrue($type->hasFeature(UserType::REGISTRATION));
        $this->assertFalse($type->hasFeature(UserType::PASSKEYS));
    }

    #[Test]
    public function it_finds_the_model_from_the_guard(): void
    {
        $this->assertEquals(Admin::class, UserTypes::get('admin')->modelClass());
        $this->assertEquals(User::class, UserTypes::get('user')->modelClass());
    }

    #[Test]
    public function it_builds_from_config(): void
    {
        $type = UserType::fromArray('public', [
            'model' => User::class,
            'guard' => 'web',
            'route_name' => '',
            'features' => ['registration' => false, 'passkeys'],
            'two_factor' => 'required',
            'max_login_attempts' => 3,
            'layouts' => ['auth' => 'layouts.guest'],
        ]);

        $this->assertEquals('login', $type->routeNameFor('login'));
        $this->assertFalse($type->hasFeature(UserType::REGISTRATION));
        $this->assertTrue($type->hasFeature(UserType::PASSKEYS));
        $this->assertTrue($type->requiresTwoFactor());
        $this->assertEquals(3, $type->maxLoginAttemptsAllowed());
        $this->assertEquals('layouts.guest', $type->layoutName('auth'));
        $this->assertEquals('layouts.app', $type->layoutName('account'));
    }

    #[Test]
    public function it_rejects_unknown_options_and_features(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UserType::fromArray('public', ['feature' => []]);
    }

    #[Test]
    public function it_rejects_unknown_features(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new UserType('public'))->enable('teleport');
    }

    #[Test]
    public function types_can_be_registered_in_code(): void
    {
        UserTypes::register('vendor', fn (UserType $type) => $type->model(User::class)->guard('web')->disable(UserType::REGISTRATION));

        $this->assertTrue(UserTypes::has('vendor'));
        $this->assertFalse(UserTypes::get('vendor')->hasFeature(UserType::REGISTRATION));
        $this->assertEquals('user', UserTypes::default()->name());
    }

    #[Test]
    public function home_can_be_a_path_a_route_or_a_closure(): void
    {
        $type = new UserType('public');

        $this->assertEquals(url('/'), $type->homeUrl());
        $this->assertEquals(route('dashboard'), $type->home('dashboard')->homeUrl());
        $this->assertEquals(url('/u/1'), $type->home(fn ($user) => url('/u/1'))->homeUrl());
    }
}
