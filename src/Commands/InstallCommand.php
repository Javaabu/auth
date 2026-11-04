<?php

namespace Javaabu\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Javaabu\Auth\UserTypes;
use Laravel\Passport\Passport;
use Laravel\Sanctum\Sanctum;

/**
 * Sets up the package, and API authentication with Sanctum, Passport or both
 */
class InstallCommand extends Command
{
    protected $signature = 'auth:install
        {--api= : API authentication: none, sanctum, passport or both}
        {--force : Overwrite published files}';

    protected $description = 'Publish the javaabu/auth config and migrations, and set up API authentication';

    public const array API_CHOICES = ['none', 'sanctum', 'passport', 'both'];

    public function handle(): int
    {
        $api = $this->option('api') ?? (
            $this->input->isInteractive()
                ? $this->choice('Which API authentication do you want?', self::API_CHOICES, 0)
                : 'none'
        );

        if (! in_array($api, self::API_CHOICES, true)) {
            $this->error("Unknown API option `{$api}`. Use none, sanctum, passport or both.");

            return self::FAILURE;
        }

        $this->info('Publishing the config and migrations...');

        $this->callSilently('vendor:publish', ['--tag' => 'auth-config', '--force' => $this->option('force')]);
        $this->callSilently('vendor:publish', ['--tag' => 'auth-migrations', '--force' => $this->option('force')]);

        $this->setApiOption($api);

        $ok = true;

        if (in_array($api, ['sanctum', 'both'], true)) {
            $ok = $this->installSanctum() && $ok;
        }

        if (in_array($api, ['passport', 'both'], true)) {
            $ok = $this->installPassport() && $ok;
        }

        $this->comment($ok ? 'javaabu/auth is installed.' : 'javaabu/auth is installed, but some steps need you (see above).');

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    protected function installSanctum(): bool
    {
        if (! class_exists(Sanctum::class)) {
            $this->warn('Install Sanctum first: composer require laravel/sanctum, then run this command again.');

            return false;
        }

        $this->info('Publishing the Sanctum migrations...');

        $this->callSilently('vendor:publish', ['--provider' => 'Laravel\Sanctum\SanctumServiceProvider']);

        $guards = implode("', '", array_map(fn ($type) => $type->guardName(), UserTypes::all()));

        $this->line('  Add Laravel\Sanctum\HasApiTokens to the user models that get API tokens,');
        $this->line("  and list the session guards in config/sanctum.php for SPA logins: 'guard' => ['{$guards}'].");

        return true;
    }

    protected function installPassport(): bool
    {
        if (! class_exists(Passport::class)) {
            $this->warn('Install Passport first: composer require laravel/passport, then run this command again.');

            return false;
        }

        $this->info('Publishing the Passport migrations and making the encryption keys...');

        $this->callSilently('vendor:publish', ['--tag' => 'passport-migrations']);
        $this->callSilently('passport:keys', ['--force' => $this->option('force')]);

        $this->line('  Add Laravel\Passport\HasApiTokens and Laravel\Passport\Contracts\OAuthenticatable to the user models,');
        $this->line('  then add a passport guard and a personal access client for each user type:');

        foreach (UserTypes::all() as $type) {
            $provider = config("auth.guards.{$type->guardName()}.provider", $type->passwordBrokerName());

            $this->line("    'api_{$type->name()}' => ['driver' => 'passport', 'provider' => '{$provider}'],");
            $this->line("    php artisan passport:client --personal --provider={$provider}");
        }

        return true;
    }

    protected function setApiOption(string $api): void
    {
        $env = $this->laravel->environmentFilePath();

        if (! File::exists($env)) {
            $this->line("  Set AUTH_API={$api} in your .env file.");

            return;
        }

        $contents = File::get($env);
        $line = "AUTH_API={$api}";

        $contents = preg_match('/^AUTH_API=.*$/m', $contents)
            ? preg_replace('/^AUTH_API=.*$/m', $line, $contents)
            : rtrim($contents) . PHP_EOL . $line . PHP_EOL;

        File::put($env, $contents);

        $this->line("  Set {$line} in .env.");
    }
}
