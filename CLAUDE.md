# php-baseline

See README.md. `devbox run -- task check` runs everything, and must pass before finishing a change; Docker must be running.

- PHP and Composer run through the `PHP` variable in `Taskfile.yml`, a `composer` image in Docker. Run one-off commands the same way rather than installing PHP.
- After changing `config/`, `tests/fixtures/conformance` or `src/Core/RulebookListing.php`, run `devbox run -- task rulebook-coverage`, then `check`.
- `tests/fixtures/conformance` breaks the rules on purpose. PHPStan and Pint leave it out.
