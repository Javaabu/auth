<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Javaabu\Auth\Tests\Fixtures\CodeLoginMethod;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;

class MultiAuthTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        UserTypes::get('user')->loginMethod(CodeLoginMethod::class);

        $this->registerAuthRoutes();

        Route::middleware(['web', 'auth:web_admin'])->get('admin/reports', fn () => 'reports');
    }

    #[Test]
    public function every_type_gets_its_own_named_routes(): void
    {
        $this->assertEquals(url('/login'), route('login'));
        $this->assertEquals(url('/admin/login'), route('admin.login'));
        $this->assertEquals(url('/settings/profile'), route('profile.edit'));
        $this->assertEquals(url('/admin/settings/profile'), route('admin.profile.edit'));
    }

    #[Test]
    public function the_type_is_resolved_from_models_and_guards(): void
    {
        $this->assertEquals('admin', UserTypes::for(Admin::class)->name());
        $this->assertEquals('user', UserTypes::for(new User)->name());
        $this->assertEquals('admin', UserTypes::forGuard('web_admin')->name());
        $this->assertEquals('admin', (new Admin)->userType()->name());
    }

    #[Test]
    public function laravel_auth_middleware_sends_guests_to_the_login_page_of_the_guard(): void
    {
        $this->get('/admin/reports')->assertRedirect('/admin/login');
    }

    #[Test]
    public function an_alternative_login_method_adds_its_routes_and_a_link_on_the_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-login-method="code"', false)
            ->assertSee(route('login.code'), false);

        $this->assertFalse(Route::has('admin.login.code'));
    }

    #[Test]
    public function an_alternative_login_method_finishes_with_the_two_factor_challenge(): void
    {
        User::factory()->withTwoFactor()->create(['email' => 'ahmed@example.com']);

        $this->post('/login/code', ['email' => 'ahmed@example.com', 'code' => '1234'])
            ->assertRedirect('/two-factor-challenge');

        $this->assertGuest('web');
    }

    #[Test]
    public function an_alternative_login_method_logs_in(): void
    {
        $user = User::factory()->create(['email' => 'ahmed@example.com']);

        $this->post('/login/code', ['email' => 'ahmed@example.com', 'code' => '1234'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function the_multi_auth_session_driver_stores_the_user_type(): void
    {
        config()->set('session.driver', 'multi_auth_database');

        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertDatabaseHas('sessions', [
            'user_id' => $admin->id,
            'user_type' => $admin->getMorphClass(),
        ]);

        $this->assertCount(1, $admin->sessions);
    }

    #[Test]
    public function logging_out_other_sessions_deletes_only_the_users_other_sessions(): void
    {
        config()->set('session.driver', 'multi_auth_database');

        $admin = Admin::factory()->create();
        $user = User::factory()->create(['id' => $admin->id]);

        DB::table('sessions')->insert([
            ['id' => 'admin-other', 'user_id' => $admin->id, 'user_type' => $admin->getMorphClass(), 'payload' => '', 'last_activity' => time()],
            ['id' => 'user-other', 'user_id' => $user->id, 'user_type' => $user->getMorphClass(), 'payload' => '', 'last_activity' => time()],
        ]);

        $this->assertCount(1, $admin->sessions);

        $admin->deleteOtherSessionRecords();

        $this->assertDatabaseMissing('sessions', ['id' => 'admin-other']);
        $this->assertDatabaseHas('sessions', ['id' => 'user-other']);
    }
}
