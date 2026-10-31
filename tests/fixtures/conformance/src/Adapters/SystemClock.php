<?php

declare(strict_types=1);

namespace App\Adapters;

final class SystemClock
{
    public function now(): int
    {
        return time();
    }
}
