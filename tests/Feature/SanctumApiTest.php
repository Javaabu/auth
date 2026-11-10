<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Tests\Models\Sanctum\Admin;
use Javaabu\Auth\Tests\Models\Sanctum\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Auth\UserTypes;
use Laravel\Sanctum\SanctumServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('optional')]
class SanctumApiTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), SanctumServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('auth.providers.admins.model', Admin::class);
        $app['config']->set('auth.guards.sanctum', ['driver' => 'sanctum', 'provider' => null]);
        $app['config']->set('javaabu-auth.api', 'sanctum');
        $app['config']->set('javaabu-auth.user_types.user.features.api_tokens', true);
        $app['config']->set('javaabu-auth.user_types.admin.features.api_tokens', true);
    }

    protected function defineRoutes($router): void
    {
        $this->registerAuthRoutes();

        Route::authApiRoutes('user', ['prefix' => 'api']);
        Route::authApiRoutes('admin', ['prefix' => 'api/admin']);

        Route::middleware(['api', 'auth:sanctum'])->get('api/me', fn () => request()->user()::class . ':' . request()->user()->id);
    }

    #[Test]
    public function it_issues_a_token_for_a_device_and_the_token_works(): void
    {
        $user = User::factory()->create(['email' => 'ahmed@example.com']);

        $token = $this->postJson('/api/tokens', [
            'email' => 'ahmed@example.com',
            'password' => 'password',
            'device_name' => 'ފޯނު',
        ])->assertCreated()->assertJsonPath('token_type', 'Bearer')->json('token');

        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'ފޯނު']);

        $this->withToken($token)->getJson('/api/me')->assertOk()->assertSee(User::class . ':' . $user->id);
    }

    #[Test]
    public function an_admin_token_is_an_admin_token(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $token = $this->postJson('/api/admin/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
            'device_name' => 'tablet',
        ])->assertCreated()->json('token');

        $this->withToken($token)->getJson('/api/me')->assertSee(Admin::class . ':' . $admin->id);
    }

    #[Test]
    public function it_rejects_wrong_credentials_and_other_types(): void
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->postJson('/api/tokens', ['email' => 'ahmed@example.com', 'password' => 'wrong', 'device_name' => 'x'])
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/admin/tokens', ['email' => 'ahmed@example.com', 'password' => 'password', 'device_name' => 'x'])
            ->assertJsonValidationErrors('email');
    }

    #[Test]
    public function it_rejects_inactive_accounts(): void
    {
        User::factory()->banned()->create(['email' => 'ahmed@example.com']);

        $this->postJson('/api/tokens', ['email' => 'ahmed@example.com', 'password' => 'password', 'device_name' => 'x'])
            ->assertJsonValidationErrors(['email' => 'Your account has been banned.']);
    }

    #[Test]
    public function it_needs_the_two_factor_code_when_the_user_has_one(): void
    {
        User::factory()->withTwoFactor('JBSWY3DPEHPK3PXP')->create(['email' => 'ahmed@example.com']);

        $this->postJson('/api/tokens', ['email' => 'ahmed@example.com', 'password' => 'password', 'device_name' => 'x'])
            ->assertJsonValidationErrors('code');

        $this->postJson('/api/tokens', [
            'email' => 'ahmed@example.com',
            'password' => 'password',
            'device_name' => 'x',
            'code' => app(TwoFactorProvider::class)->currentCode('JBSWY3DPEHPK3PXP'),
        ])->assertCreated();
    }

    #[Test]
    public function it_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('phone')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/tokens/current')->assertNoContent();

        $this->assertCount(0, $user->tokens()->get());
    }

    #[Test]
    public function users_manage_their_tokens_on_the_security_page(): void
    {
        $user = User::factory()->create();
        $session = [RequirePassword::sessionKey(UserTypes::get('user')) => time()];

        $this->actingAs($user, 'web')->withSession($session)
            ->post('/settings/api-tokens', ['token_name' => 'CI'])
            ->assertSessionHas('plain_text_token');

        $token = $user->tokens()->firstOrFail();

        $this->get('/settings/security')->assertOk()->assertSee('data-api-tokens', false)->assertSee('CI');

        $this->delete("/settings/api-tokens/{$token->id}")->assertSessionHasNoErrors();

        $this->assertCount(0, $user->tokens()->get());
    }

    #[Test]
    public function admins_log_users_out_of_their_api_tokens_too(): void
    {
        $this->app['config']->set('permissions.guards', ['web_admin']);
        Route::userAccountActions('user', ['prefix' => 'admin/users', 'middleware' => ['web', 'auth.type:admin']]);
        $this->artisan('permissions:sync');

        $user = User::factory()->create();
        $user->createToken('phone');
        $admin = Admin::factory()->create();
        $admin->givePermissionTo('log_out_users');

        $this->actingAs($admin, 'web_admin')
            ->postJson("/admin/users/{$user->id}/log-out")
            ->assertOk();

        $this->assertCount(0, $user->tokens()->get());
    }

    #[Test]
    public function users_cannot_delete_someone_elses_token(): void
    {
        $other = User::factory()->create();
        $token = $other->createToken('phone')->accessToken;

        $this->actingAs(User::factory()->create(), 'web')
            ->deleteJson("/settings/api-tokens/{$token->id}")
            ->assertNotFound();
    }
}
