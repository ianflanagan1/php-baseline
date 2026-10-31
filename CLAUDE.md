# php-baseline

See README.md. `devbox run -- task check` runs everything, and must pass before finishing a change.

- PHP and Composer come from the project's devbox. Run one-off commands as `devbox run -- php …` or `devbox run -- composer …` rather than installing PHP.
- After changing `config/`, `tests/fixtures/conformance` or `src/Core/RulebookListing.php`, run `devbox run -- task rulebook-coverage`, then `check`.
- `tests/fixtures/conformance` breaks the rules on purpose. PHPStan and Pint leave it out.
