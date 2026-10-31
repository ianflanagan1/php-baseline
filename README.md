# php-baseline

The architecture checks every PHP project of mine shares: a functional core, an imperative shell, and adapters behind ports. It is the PHP counterpart of [go-baseline](https://github.com/ianflanagan1/go-baseline) and [@ianflanagan1/ts-baseline](https://github.com/ianflanagan1/ts-baseline), and enforces the [architecture rulebook](https://github.com/ianflanagan1/architecture-rulebook) in PHP's terms.

| Export | What it is |
|---|---|
| `config/deptrac.yaml` | deptrac layers for the roles, by directory, and which may depend on which |
| `config/phpstan.neon` | spaze/phpstan-disallowed-calls bans on each effect, outside the roles allowed to perform it |
| `php-baseline rulebook` | Prints how the baseline enforces each clause of the rulebook, as the coverage listing the rulebook imports |

## Roles

A file's directory decides its role:

| Role | Directory | May depend on | May perform effects |
|---|---|---|---|
| Core | `src/Core/` | The core | None |
| Shell | The rest of `src/` | The core and the shell | None: only through ports |
| Adapter | `src/Adapters/` | The core and other adapters | All but reading configuration |
| Composition root | `bin/` and `public/` | Anything | All |

Tests may do anything.

The effects are the rulebook's: the clock, randomness, the filesystem, the network (including databases and the request superglobals), processes (including `exit`), the console (including `echo`), global logging, configuration (`getenv()`, `$_ENV`, `$_SERVER`) and global state (`global`, `$GLOBALS`). `new DateTimeImmutable()` reads the clock, so the core parses dates with `DateTimeImmutable::createFromFormat()` and takes the current time as a value.

## Use

```sh
composer require --dev ianflanagan1/php-baseline phpstan/extension-installer
```

phpstan/extension-installer includes `config/phpstan.neon` and spaze/phpstan-disallowed-calls. Without it, include both in `phpstan.neon`:

```neon
includes:
    - vendor/spaze/phpstan-disallowed-calls/extension.neon
    - vendor/ianflanagan1/php-baseline/config/phpstan.neon
```

`deptrac.yaml`:

```yaml
imports:
  - vendor/ianflanagan1/php-baseline/config/deptrac.yaml

deptrac:
  paths:
    - src
    - bin
```

Run PHPStan and deptrac from the project's root: the paths in both configs are from there. A project with other directories for adapters or entry points sets `phpBaselineEffectPaths` and `phpBaselineCompositionRootPaths` in its `phpstan.neon`.

## What it doesn't check yet

[The rulebook's coverage table](https://github.com/ianflanagan1/architecture-rulebook/blob/main/COVERAGE.md) has the full list. The main gaps:

- Libraries: deptrac leaves out classes in no layer, so the core and shell can use any library, including one that does I/O.
- Static properties and static variables, which hold global state.
- The bans list the common functions for each effect, not every one.
- Function size, import cycles, a project's kind, and the rules go-baseline's analyzers enforce: the core takes values rather than ports, and the shell branches only on what the core returns.
- There is no `verify` yet, to check that a project includes these configs and doesn't weaken them.

## The architecture rulebook

`src/Core/RulebookListing.php` records which checks enforce each of the rulebook's clauses and the tests that prove it. `rulebook-coverage.json` holds that listing, for the rulebook to import. A test fails when the file is out of date or names a test that doesn't exist.

`tests/ConformanceTest.php` is the proof. It runs deptrac and PHPStan, with the shipped configs, over `tests/fixtures/conformance`, which has a file for each violation the rulebook describes. Each test is named for its clause.

When a check changes what it enforces, or the rulebook adds a clause:

1. Add the violation to `tests/fixtures/conformance` and a test named for its clause.
2. Update the claim in `src/Core/RulebookListing.php`.
3. `devbox run -- task rulebook-coverage`, then `check`.
4. After the release, import the listing into the rulebook.

## Development

PHP and Composer come from the project's devbox, so neither needs installing.

```sh
devbox run -- task setup
devbox run -- task check   # composer.json, the tests (including the conformance tests), PHPStan, deptrac and Pint
```

The package follows the architecture it ships: `src/Core/` is pure, and `bin/php-baseline` is the composition root.
