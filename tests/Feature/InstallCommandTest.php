<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\File;
use Javaabu\Auth\Tests\TestCase;
use Laravel\Passport\PassportServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;
use PHPUnit\Framework\Attributes\Test;

class InstallCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), SanctumServiceProvider::class, PassportServiceProvider::class];
    }

    protected function tearDown(): void
    {
        File::delete(config_path('javaabu-auth.php'));
        File::delete(glob(database_path('migrations/*_create_passkeys_table.php')));
        File::delete(glob(database_path('migrations/*_create_personal_access_tokens_table.php')));
        File::delete(glob(database_path('migrations/*_oauth_*.php')));
        File::delete(storage_path('oauth-private.key'));
        File::delete(storage_path('oauth-public.key'));

        parent::tearDown();
    }

    #[Test]
    public function it_installs_with_sanctum(): void
    {
        $this->artisan('auth:install', ['--api' => 'sanctum'])
            ->expectsOutputToContain('Laravel\Sanctum\HasApiTokens')
            ->assertSuccessful();

        $this->assertFileExists(config_path('javaabu-auth.php'));
        $this->assertNotEmpty(glob(database_path('migrations/*_create_passkeys_table.php')));
        $this->assertNotEmpty(glob(database_path('migrations/*_create_personal_access_tokens_table.php')));
    }

    #[Test]
    public function it_installs_with_both_and_prints_a_passport_guard_per_user_type(): void
    {
        $this->artisan('auth:install', ['--api' => 'both'])
            ->expectsOutputToContain("'api_admin' => ['driver' => 'passport', 'provider' => 'admins']")
            ->expectsOutputToContain('passport:client --personal --provider=users')
            ->assertSuccessful();

        $this->assertFileExists(storage_path('oauth-private.key'));
    }

    #[Test]
    public function it_rejects_unknown_api_options(): void
    {
        $this->artisan('auth:install', ['--api' => 'jwt'])->assertFailed();
    }
}
