<?php

declare(strict_types=1);

namespace IanFlanagan\PhpBaseline\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Shows the shipped deptrac and PHPStan configs catching each violation of the rulebook's clauses
 * that the listing claims. tests/fixtures/conformance has a file for each violation, and each test
 * is named for its clause.
 */
final class ConformanceTest extends TestCase
{
    private const string FIXTURE = __DIR__.'/fixtures/conformance';

    /** @var array<string, list<string>>|null deptrac's violations, by file, as "<layer> -> <layer>" */
    private static ?array $layerViolations = null;

    /** @var array<string, list<string>>|null PHPStan's errors, by file, as their identifiers */
    private static ?array $phpstanErrors = null;

    public function test_every_unit_has_one_role(): void
    {
        $this->assertSame([], self::layerViolations('src/Core/Pure.php'));
        $this->assertSame([], self::layerViolations('src/Adapters/SystemClock.php'));
        $this->assertSame([], self::layerViolations('src/Greeter.php'));
        $this->assertSame([], self::layerViolations('bin/app.php'));
    }

    public function test_core_imports_only_core(): void
    {
        $this->assertSame(['Core -> Shell'], self::layerViolations('src/Core/ImportsShell.php'));
        $this->assertSame(['Core -> Adapter'], self::layerViolations('src/Core/ImportsAdapter.php'));
    }

    public function test_shell_imports_no_adapter(): void
    {
        $this->assertSame(['Shell -> Adapter'], self::layerViolations('src/Shell/ImportsAdapter.php'));
    }

    public function test_adapter_imports_no_shell(): void
    {
        $this->assertSame(['Adapter -> Shell'], self::layerViolations('src/Adapters/CallsShell.php'));
    }

    public function test_core_performs_no_effects(): void
    {
        $this->assertEffectsAreReported('src/Core');
        $this->assertSame([], self::phpstanErrors('src/Core/Pure.php'));
    }

    public function test_shell_performs_no_effects(): void
    {
        $this->assertEffectsAreReported('src/Shell');
        $this->assertSame([], self::phpstanErrors('src/Greeter.php'));
    }

    public function test_only_composition_root_reads_configuration(): void
    {
        $this->assertSame(['disallowed.function', 'disallowed.variable'], self::phpstanErrors('src/Core/Configuration.php'));
        $this->assertSame(['disallowed.function', 'disallowed.variable'], self::phpstanErrors('src/Shell/Configuration.php'));
        $this->assertSame(['disallowed.function'], self::phpstanErrors('src/Adapters/ReadsConfiguration.php'));
        $this->assertSame([], self::phpstanErrors('bin/app.php'));
        $this->assertSame([], self::phpstanErrors('src/Adapters/SystemClock.php'));
    }

    private function assertEffectsAreReported(string $dir): void
    {
        $expected = [
            'Clock' => ['disallowed.function', 'disallowed.new'],
            'Randomness' => ['disallowed.function'],
            'Filesystem' => ['disallowed.function'],
            'Network' => ['disallowed.class', 'disallowed.class', 'disallowed.function'],
            'Process' => ['disallowed.function'],
            'Console' => ['disallowed.echo'],
            'Logging' => ['disallowed.function'],
            'Configuration' => ['disallowed.function', 'disallowed.variable'],
            'GlobalState' => ['disallowed.global'],
        ];
        foreach ($expected as $effect => $identifiers) {
            $this->assertSame($identifiers, self::phpstanErrors("{$dir}/{$effect}.php"), $effect);
        }
    }

    /**
     * @return list<string>
     */
    private static function layerViolations(string $file): array
    {
        self::$layerViolations ??= self::runDeptrac();

        return self::$layerViolations[$file] ?? [];
    }

    /**
     * @return list<string>
     */
    private static function phpstanErrors(string $file): array
    {
        self::$phpstanErrors ??= self::runPhpstan();

        return self::$phpstanErrors[$file] ?? [];
    }

    /**
     * @return array<string, list<string>>
     */
    private static function runDeptrac(): array
    {
        $report = tempnam(sys_get_temp_dir(), 'deptrac');
        self::runTool(['vendor/bin/deptrac', 'analyse', '--no-progress', '--formatter=json', '--output='.$report]);
        $violations = [];
        foreach (self::messagesByFile((string) file_get_contents($report)) as $file => $messages) {
            foreach ($messages as $message) {
                if (preg_match('/\((\w+) on (\w+)\)$/', $message['message'], $layers) !== 1) {
                    self::fail("deptrac reported {$message['message']}, which names no layers");
                }
                $violations[self::relative($file)][] = "{$layers[1]} -> {$layers[2]}";
            }
        }

        return $violations;
    }

    /**
     * @return array<string, list<string>>
     */
    private static function runPhpstan(): array
    {
        $errors = [];
        foreach (self::messagesByFile(self::runTool(['vendor/bin/phpstan', 'analyse', '--no-progress', '--error-format=json'])) as $file => $messages) {
            foreach ($messages as $message) {
                $errors[self::relative($file)][] = $message['identifier'] ?? '';
            }
        }

        return $errors;
    }

    /**
     * Runs a tool from this package's vendor/bin in the fixture, and returns its standard output.
     *
     * @param  list<string>  $command
     */
    private static function runTool(array $command): string
    {
        $command[0] = dirname(__DIR__).'/'.$command[0];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, self::FIXTURE);
        self::assertIsResource($process);
        $output = (string) stream_get_contents($pipes[1]);
        $errors = (string) stream_get_contents($pipes[2]);
        $status = proc_close($process);
        self::assertContains($status, [0, 1], "{$command[0]} failed:\n{$errors}");

        return $output;
    }

    /**
     * Reads the messages from a report that deptrac or PHPStan wrote as JSON, which both shape
     * the same way.
     *
     * @return array<string, list<array{message: string, identifier?: string}>>
     */
    private static function messagesByFile(string $json): array
    {
        /** @var array{files: array<string, array{messages: list<array{message: string, identifier?: string}>}>} $report */
        $report = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return array_map(static fn (array $file): array => $file['messages'], $report['files']);
    }

    private static function relative(string $file): string
    {
        $prefix = realpath(self::FIXTURE).'/';

        return str_starts_with($file, $prefix) ? substr($file, strlen($prefix)) : $file;
    }
}
