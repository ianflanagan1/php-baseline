<?php

declare(strict_types=1);

namespace App\Core;

final class Configuration
{
    public function home(): string|false
    {
        return getenv('HOME');
    }

    public function env(): mixed
    {
        return $_ENV['HOME'] ?? null;
    }
}
