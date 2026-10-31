<?php

declare(strict_types=1);

namespace App\Core;

final class Clock
{
    public function now(): int
    {
        return time();
    }

    public function today(): \DateTimeImmutable
    {
        return new \DateTimeImmutable;
    }
}
