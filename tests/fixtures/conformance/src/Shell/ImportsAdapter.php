<?php

declare(strict_types=1);

namespace App\Shell;

use App\Adapters\SystemClock;

final class ImportsAdapter
{
    public function now(): int
    {
        return (new SystemClock)->now();
    }
}
