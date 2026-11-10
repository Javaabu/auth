<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\Support\ApiTokens;
use Javaabu\Auth\Tests\Models\Passport\Admin;
use Javaabu\Auth\Tests\Models\Passport\User;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Auth\UserTypes;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('optional')]
class PassportApiTest extends TestCase
{
    protected static ?string $key_path = null;

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), PassportServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $config = $app['config'];

        $config->set('javaabu-auth.api', 'passport');
        $config->set('auth.providers.users.model', User::class);
        $config->set('auth.providers.admins.model', Admin::class);
        $config->set('auth.guards.api', ['driver' => 'passport', 'provider' => 'users']);
        $config->set('auth.guards.api_admin', ['driver' => 'passport', 'provider' => 'admins']);
        $config->set('javaabu-auth.user_types.user.features.api_tokens', true);

        Passport::loadKeysFrom(static::keyPath());
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/laravel/passport/database/migrations');
    }

    protected function defineRoutes($router): void
    {
        $this->registerAuthRoutes();

        Route::middleware(['api', 'auth:api'])->get('api/me', fn () => request()->user()::class);
        Route::middleware(['api', 'auth:api_admin'])->get('api/admin/me', fn () => request()->user()::class);
    }

    protected static function keyPath(): string
    {
        if (static::$key_path) {
            return static::$key_path;
        }

        $path = sys_get_temp_dir() . '/javaabu-auth-passport-' . getmypid();

        if (! is_dir($path)) {
            mkdir($path);
        }

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $private);

        file_put_contents("{$path}/oauth-private.key", $private);
        file_put_contents("{$path}/oauth-public.key", openssl_pkey_get_details($key)['key']);
        chmod("{$path}/oauth-private.key", 0600);
        chmod("{$path}/oauth-public.key", 0600);

        return static::$key_path = $path;
    }

    protected function personalAccessClients(): void
    {
        $clients = app(ClientRepository::class);

        $clients->createPersonalAccessGrantClient('Users', 'users');
        $clients->createPersonalAccessGrantClient('Admins', 'admins');
    }

    protected function makeUser(string $class): User|Admin
    {
        return $class::forceCreate([
            'name' => 'އަޙްމަދު',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'email_verified_at' => now(),
            'status' => UserStatuses::APPROVED,
        ]);
    }

    #[Test]
    public function personal_access_tokens_work_per_provider(): void
    {
        $this->personalAccessClients();

        $user = $this->makeUser(User::class);
        $admin = $this->makeUser(Admin::class);

        $user_token = ApiTokens::create($user, 'phone');
        $admin_token = ApiTokens::create($admin, 'tablet');

        $this->withToken($user_token)->getJson('/api/me')->assertOk()->assertSee(User::class);
        $this->withToken($admin_token)->getJson('/api/admin/me')->assertOk()->assertSee(Admin::class);

        // A user's token doesn't pass the admin guard
        app('auth')->forgetGuards();
        $this->withToken($user_token)->getJson('/api/admin/me')->assertUnauthorized();
    }

    #[Test]
    public function client_credentials_tokens_are_issued_by_passport(): void
    {
        $client = app(ClientRepository::class)->createClientCredentialsGrantClient('Machine');

        $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->getKey(),
            'client_secret' => $client->plainSecret,
        ])->assertOk()->assertJsonStructure(['access_token', 'expires_in', 'token_type']);
    }

    #[Test]
    public function users_create_and_revoke_passport_tokens_on_the_security_page(): void
    {
        $this->personalAccessClients();

        $user = $this->makeUser(User::class);
        $session = [RequirePassword::sessionKey(UserTypes::get('user')) => time()];

        $this->actingAs($user, 'web')->withSession($session)
            ->post('/settings/api-tokens', ['token_name' => 'CI'])
            ->assertSessionHas('plain_text_token');

        $this->get('/settings/security')->assertOk()->assertSee('CI');

        $token = $user->tokens()->firstOrFail();

        $this->delete("/settings/api-tokens/{$token->id}")->assertSessionHasNoErrors();

        $this->assertTrue($token->fresh()->revoked);
        $this->assertEquals([], ApiTokens::list($user));
    }

    #[Test]
    public function the_driver_is_found_from_the_model(): void
    {
        $this->assertEquals('passport', ApiTokens::driverFor(User::class));
        $this->assertNull(ApiTokens::driverFor(\Javaabu\Auth\Tests\Models\User::class));
        $this->assertEquals('sanctum', ApiTokens::driverFor(\Javaabu\Auth\Tests\Models\Sanctum\User::class));
        $this->assertEquals(['passport'], ApiTokens::enabledDrivers());
    }
}
