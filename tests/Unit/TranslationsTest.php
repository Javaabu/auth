<?php

namespace Javaabu\Auth\Tests\Unit;

use Javaabu\Auth\Policies\UserAccountPolicy;
use Javaabu\Auth\Support\Strings;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TranslationsTest extends TestCase
{
    /** @return array<int, string> */
    protected function sourceStrings(): array
    {
        $files = [
            ...glob(__DIR__ . '/../../src/{,*/,*/*/,*/*/*/}*.php', GLOB_BRACE),
            ...glob(__DIR__ . '/../../resources/views/{,*/,*/*/}*.blade.php', GLOB_BRACE),
        ];

        $strings = array_values(Strings::ALL);

        foreach ($files as $file) {
            preg_match_all("/__\(\s*'((?:\\\\'|[^'])+)'/", file_get_contents($file), $single);
            preg_match_all('/__\(\s*"((?:\\\\"|[^"])+)"/', file_get_contents($file), $double);

            foreach ([...$single[1], ...$double[1]] as $string) {
                $strings[] = str_replace("\\'", "'", $string);
            }
        }

        return array_values(array_unique(array_filter($strings, fn ($string) => ! str_starts_with($string, '$'))));
    }

    #[Test]
    public function every_string_has_a_dhivehi_translation(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);

        $missing = array_values(array_filter($this->sourceStrings(), fn ($string) => ! isset($dv[$string])));

        $this->assertEquals([], $missing, 'Add these to lang/dv.json');
    }

    #[Test]
    public function the_dhivehi_translations_are_in_thaana_and_keep_placeholders(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);

        foreach ($dv as $english => $dhivehi) {
            $this->assertMatchesRegularExpression('/\p{Thaana}/u', $dhivehi, "Not in Thaana: {$english}");

            preg_match_all('/:[a-z_]+/', $english, $placeholders);

            foreach ($placeholders[0] as $placeholder) {
                $this->assertStringContainsString($placeholder, $dhivehi, "Lost {$placeholder}: {$english}");
            }
        }
    }

    #[Test]
    public function the_english_file_has_the_same_keys(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);
        $en = json_decode(file_get_contents(__DIR__ . '/../../lang/en.json'), true);

        $this->assertEquals(array_keys($dv), array_keys($en));
    }

    #[Test]
    public function permission_descriptions_are_translated_for_the_default_types(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);

        foreach (['users', 'admins'] as $resource) {
            foreach (UserAccountPolicy::DESCRIPTIONS as $description) {
                $this->assertArrayHasKey(strtr($description, [':resource' => $resource]), $dv);
            }
        }
    }

    #[Test]
    public function views_and_pages_use_logical_direction_utilities_only(): void
    {
        $files = [
            ...glob(__DIR__ . '/../../resources/views/{,*/,*/*/}*.blade.php', GLOB_BRACE),
            ...glob(__DIR__ . '/../../resources/js/{,*/,*/*/}*.{ts,tsx}', GLOB_BRACE),
        ];

        $physical = '/(?<![\w-])(?:-?m[lr]-|p[lr]-|left-|right-|text-left|text-right|float-left|float-right|border-[lr]-|rounded-[lr]-)/';

        foreach ($files as $file) {
            preg_match_all('/class(?:Name)?="([^"]*)"/', file_get_contents($file), $classes);

            foreach ($classes[1] as $class) {
                $this->assertDoesNotMatchRegularExpression($physical, $class, basename($file) . ": {$class}");
            }
        }
    }

    #[Test]
    public function the_js_test_strings_match_the_dhivehi_strings(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);
        $fixture = json_decode(file_get_contents(__DIR__ . '/../js/strings.dv.json'), true);

        $expected = array_map(fn (string $english) => $dv[$english] ?? $english, Strings::ALL);

        $this->assertEquals($expected, $fixture, 'Regenerate tests/js/strings.dv.json (see AGENTS.md)');
    }
}
