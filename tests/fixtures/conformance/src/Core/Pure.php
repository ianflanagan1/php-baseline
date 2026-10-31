<?php

declare(strict_types=1);

namespace App\Core;

final class Pure
{
    public function upper(string $s): string
    {
        return strtoupper($s);
    }

    public function parse(string $date): \DateTimeImmutable|false
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $date);
    }
}
