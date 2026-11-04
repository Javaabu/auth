<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Javaabu\Auth\Tests\Fixtures\DenyAllAccountPolicy;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Permissions\Permissions;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

class UserAccountsTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permissions.guards', ['web_admin']);
    }

    protected function defineRoutes($router): void
    {
        $this->registerAuthRoutes();

        Route::userAccountActions('user', ['prefix' => 'admin/users', 'middleware' => ['web', 'auth.type:admin']]);
    }

    protected function admin(array $permissions = []): Admin
    {
        $this->artisan('permissions:sync');

        $admin = Admin::factory()->create();

        if ($permissions) {
            $admin->givePermissionTo($permissions);
        }

        return $admin;
    }

    #[Test]
    public function it_registers_permissions_for_each_user_type(): void
    {
        $this->assertTrue(Permissions::has('unlock_users'));
        $this->assertTrue(Permissions::has('reset_two_factor_admins'));
        $this->assertEquals('Unlock locked users', Permissions::description('unlock_users'));
    }

    #[Test]
    public function it_needs_the_permission_to_unlock_an_account(): void
    {
        $user = User::factory()->create(['login_attempts' => 10]);

        $this->actingAs($this->admin(), 'web_admin')
            ->post("/admin/users/{$user->id}/unlock")
            ->assertForbidden();

        $this->actingAs($this->admin(['unlock_users']), 'web_admin')
            ->post("/admin/users/{$user->id}/unlock")
            ->assertRedirect();

        $this->assertNull($user->fresh()->login_attempts);
    }

    #[Test]
    public function it_resets_two_factor_authentication(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($this->admin(['reset_two_factor_users']), 'web_admin')
            ->deleteJson("/admin/users/{$user->id}/two-factor")
            ->assertOk();

        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    #[Test]
    public function it_makes_a_user_choose_a_new_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin(['require_password_update_users']), 'web_admin')
            ->postJson("/admin/users/{$user->id}/require-password-update")
            ->assertOk();

        $this->assertTrue($user->fresh()->requiresPasswordUpdate());
    }

    #[Test]
    public function it_logs_a_user_out_everywhere(): void
    {
        $user = User::factory()->create(['remember_token' => 'old']);
        $user->createToken('phone');

        $this->actingAs($this->admin(['log_out_users']), 'web_admin')
            ->postJson("/admin/users/{$user->id}/log-out")
            ->assertOk();

        $this->assertNotEquals('old', $user->fresh()->remember_token);
        $this->assertCount(0, $user->tokens()->get());
    }

    #[Test]
    public function the_routes_need_the_admin_guard(): void
    {
        $user = User::factory()->create();

        $this->actingAs(User::factory()->create(), 'web')
            ->post("/admin/users/{$user->id}/unlock")
            ->assertRedirect('/admin/login');
    }

    #[Test]
    public function the_policy_can_be_replaced_with_gate_define(): void
    {
        Gate::define('auth.unlock', [DenyAllAccountPolicy::class, 'unlock']);

        $user = User::factory()->create();

        $this->actingAs($this->admin(['unlock_users']), 'web_admin')
            ->post("/admin/users/{$user->id}/unlock")
            ->assertForbidden();
    }

    protected function usesDenyAllPolicy($app): void
    {
        $app['config']->set('javaabu-auth.policy', DenyAllAccountPolicy::class);
    }

    #[Test]
    #[DefineEnvironment('usesDenyAllPolicy')]
    public function the_policy_can_be_replaced_from_config(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin(['unlock_users']), 'web_admin')
            ->post("/admin/users/{$user->id}/unlock")
            ->assertForbidden();
    }
}
