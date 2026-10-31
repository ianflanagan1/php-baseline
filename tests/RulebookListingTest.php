<?php

declare(strict_types=1);

namespace IanFlanagan\PhpBaseline\Tests;

use IanFlanagan\PhpBaseline\Core\RulebookListing;
use PHPUnit\Framework\TestCase;

final class RulebookListingTest extends TestCase
{
    public function test_the_committed_listing_is_current(): void
    {
        $this->assertSame(
            file_get_contents(dirname(__DIR__).'/'.RulebookListing::PATH),
            RulebookListing::json(),
            RulebookListing::PATH.' is out of date; run `devbox run -- task rulebook-coverage`',
        );
    }

    public function test_every_proof_names_a_test_that_exists(): void
    {
        preg_match_all('#"tests/(\w+)\.php: (\w+)"#', RulebookListing::json(), $proofs, PREG_SET_ORDER);
        $this->assertNotEmpty($proofs);
        foreach ($proofs as [, $class, $method]) {
            $this->assertTrue(method_exists(__NAMESPACE__.'\\'.$class, $method), "tests/{$class}.php has no test {$method}");
        }
    }
}
