<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Javaabu\Auth\Tests\TestCase;
use Javaabu\Manifest\Enums\NpmScopes;
use Javaabu\Manifest\Facades\Manifest;
use PHPUnit\Framework\Attributes\Test;

class ManifestCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        File::delete(base_path('package.json'));

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $dependencies
     * @return array<int, array<string, mixed>>
     */
    protected function check(array $dependencies): array
    {
        File::put(base_path('package.json'), json_encode(['dependencies' => $dependencies]));

        Artisan::call('manifest:check', ['--json' => true]);

        return json_decode(Artisan::output(), true);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, array<string, mixed>>
     */
    protected function neededByAuth(array $rows): array
    {
        $rows = array_filter($rows, fn (array $row) => in_array('auth', $row['needed_by'], true));

        return array_column($rows, null, 'name');
    }

    #[Test]
    public function it_declares_the_react_packages_for_the_pages_only(): void
    {
        $package = Manifest::package('auth');

        $this->assertSame([], $package->npmDependencies(NpmScopes::ASSETS));
        $this->assertSame([], $package->npmPeerDependencies(NpmScopes::ASSETS));
        $this->assertSame(['@javaabu/forms', '@javaabu/onu'], array_keys($package->npmDependencies(NpmScopes::PAGES)));
        $this->assertSame(['react', 'react-dom', '@inertiajs/react'], array_keys($package->npmPeerDependencies(NpmScopes::PAGES)));
    }

    #[Test]
    public function it_asks_blade_apps_for_the_build_tools_only(): void
    {
        $rows = $this->neededByAuth($this->check(['vite' => '^7.0', 'laravel-vite-plugin' => '^2.0']));

        $this->assertEqualsCanonicalizing(['vite', 'laravel-vite-plugin'], array_keys($rows));
    }

    #[Test]
    public function it_asks_inertia_apps_for_the_react_packages(): void
    {
        $rows = $this->neededByAuth($this->check(['@inertiajs/react' => '^2.0']));

        $this->assertEqualsCanonicalizing(['vite', 'laravel-vite-plugin', '@javaabu/forms', '@javaabu/onu', 'react', 'react-dom', '@inertiajs/react'], array_keys($rows));
        $this->assertSame('missing', $rows['@javaabu/forms']['status']);
        $this->assertContains('pages', $rows['@javaabu/forms']['scopes']);
    }

    #[Test]
    public function the_blade_scripts_import_no_npm_packages(): void
    {
        foreach (['auth.ts', 'passkeys.ts', 'lib/webauthn.ts'] as $script) {
            $source = File::get(__DIR__ . '/../../resources/js/' . $script);

            preg_match_all('/(?:from|import)\s+["\']([^"\']+)["\']/', $source, $matches);

            foreach ($matches[1] as $import) {
                $this->assertStringStartsWith('.', $import, "{$script} imports {$import}, which Blade apps would need to install; declare it in the assets scope.");
            }
        }
    }
}
