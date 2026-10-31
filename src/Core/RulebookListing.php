<?php

declare(strict_types=1);

namespace IanFlanagan\PhpBaseline\Core;

/**
 * How php-baseline enforces each clause of the architecture rulebook
 * (github.com/ianflanagan1/architecture-rulebook), as the coverage listing the rulebook imports.
 *
 * COUPLED:coverage-listing-shape. = architecture-rulebook:schema/coverage.schema.json (fields, check kinds and coverage levels)
 */
final class RulebookListing
{
    public const string PATH = 'rulebook-coverage.json';

    public static function json(): string
    {
        return json_encode(self::listing(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    }

    /**
     * @return array<string, mixed>
     */
    private static function listing(): array
    {
        return [
            'language' => 'php',
            'baseline' => 'ianflanagan1/php-baseline',
            'roles' => [
                'core' => 'Files under src/Core/.',
                'shell' => 'Every other file under src/.',
                'adapter' => 'Files under src/Adapters/.',
                'composition-root' => 'Scripts in bin/ and public/.',
            ],
            'checks' => [
                'deptrac-layers' => [
                    'kind' => 'tool',
                    'tool' => 'deptrac',
                    'summary' => 'Layers by directory: src/Core/ is the core, src/Adapters/ the adapters and the rest of src/ the shell. The core depends on nothing else; the shell and adapters depend only on the core.',
                ],
                'disallowed-effects' => [
                    'kind' => 'tool',
                    'tool' => 'phpstan',
                    'summary' => 'spaze/phpstan-disallowed-calls bans the functions, classes, constants and superglobals that perform each effect, and the global keyword, outside src/Adapters/, bin/, public/ and tests/.',
                ],
                'disallowed-configuration' => [
                    'kind' => 'tool',
                    'tool' => 'phpstan',
                    'summary' => 'spaze/phpstan-disallowed-calls bans getenv(), putenv(), ini_get(), ini_set(), $_ENV and $_SERVER outside bin/, public/ and tests/.',
                ],
            ],
            'clauses' => [
                'every-unit-has-one-role' => self::full(['deptrac-layers'], 'test_every_unit_has_one_role'),
                'project-kind-limits-roles' => self::none(),
                'core-imports-only-core' => self::full(['deptrac-layers'], 'test_core_imports_only_core'),
                'core-performs-no-effects' => self::partial(['disallowed-effects', 'disallowed-configuration'], self::effectsGap(), 'test_core_performs_no_effects'),
                'core-libraries-are-pure' => self::noneBecause('deptrac leaves out classes in no layer, so the core can use any library.'),
                'shell-imports-no-adapter' => self::full(['deptrac-layers'], 'test_shell_imports_no_adapter'),
                'shell-performs-no-effects' => self::partial(['disallowed-effects', 'disallowed-configuration'], self::effectsGap(), 'test_shell_performs_no_effects'),
                'shell-libraries-are-pure' => self::noneBecause('deptrac leaves out classes in no layer, so the shell can use any library.'),
                'ports-declared-by-their-user' => self::full(['deptrac-layers'], 'test_shell_imports_no_adapter') + ['note' => "The shell can't depend on an adapter, so it can't take an interface an adapter declares."],
                'ports-named-in-domain-terms' => self::none(),
                'core-receives-only-pure-values' => self::none(),
                'shell-branches-only-on-core-results' => self::none(),
                'adapter-imports-no-shell' => self::full(['deptrac-layers'], 'test_adapter_imports_no_shell'),
                'adapter-size-is-limited' => self::none(),
                'only-composition-root-reads-configuration' => self::full(['disallowed-configuration'], 'test_only_composition_root_reads_configuration'),
                'composition-root-size-is-limited' => self::none(),
                'wired-without-a-container' => self::none(),
                'no-import-cycles' => self::none(),
            ],
        ];
    }

    private static function effectsGap(): string
    {
        return 'The bans list the common functions and classes for each effect, not every one. Static properties and static variables, which hold global state, pass.';
    }

    /**
     * @param  list<string>  $checks
     * @return array<string, mixed>
     */
    private static function full(array $checks, string $test): array
    {
        return ['coverage' => 'full', 'checks' => $checks, 'proof' => [self::conformance($test)]];
    }

    /**
     * @param  list<string>  $checks
     * @return array<string, mixed>
     */
    private static function partial(array $checks, string $gap, string $test): array
    {
        return ['coverage' => 'partial', 'checks' => $checks, 'gap' => $gap, 'proof' => [self::conformance($test)]];
    }

    /**
     * @return array<string, string>
     */
    private static function none(): array
    {
        return ['coverage' => 'none'];
    }

    /**
     * @return array<string, string>
     */
    private static function noneBecause(string $note): array
    {
        return ['coverage' => 'none', 'note' => $note];
    }

    private static function conformance(string $test): string
    {
        return 'tests/ConformanceTest.php: '.$test;
    }
}
