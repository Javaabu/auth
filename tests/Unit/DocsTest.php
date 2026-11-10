<?php

namespace Javaabu\Auth\Tests\Unit;

use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DocsTest extends TestCase
{
    #[Test]
    public function llms_txt_links_every_docs_page(): void
    {
        $llms = file_get_contents(__DIR__ . '/../../llms.txt');

        foreach (glob(__DIR__ . '/../../docs/{,*/}*.md', GLOB_BRACE) as $file) {
            $path = 'docs/' . ltrim(str_replace(realpath(__DIR__ . '/../../docs'), '', realpath($file)), '/');

            $this->assertStringContainsString("({$path})", $llms, "llms.txt doesn't link {$path}");
        }
    }

    #[Test]
    public function the_boost_skill_has_its_front_matter(): void
    {
        $skill = file_get_contents(__DIR__ . '/../../resources/boost/skills/javaabu-auth/SKILL.md');

        $this->assertStringStartsWith("---\nname: javaabu-auth\n", $skill);
        $this->assertFileExists(__DIR__ . '/../../resources/boost/guidelines/core.blade.php');
    }

    #[Test]
    public function the_docs_ship_with_the_composer_package(): void
    {
        $attributes = file_get_contents(__DIR__ . '/../../.gitattributes');

        foreach (['/docs', '/llms.txt', '/resources', '/lang', '/src', '/config', '/database'] as $path) {
            $this->assertDoesNotMatchRegularExpression('#^' . preg_quote($path, '#') . '\\s+export-ignore#m', $attributes, "{$path} must ship with the package");
        }

        $this->assertMatchesRegularExpression('#^/tests\\s+export-ignore#m', $attributes);
    }
}
