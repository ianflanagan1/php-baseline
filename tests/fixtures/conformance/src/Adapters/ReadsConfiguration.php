<?php

declare(strict_types=1);

namespace App\Adapters;

final class ReadsConfiguration
{
    public function dsn(): string|false
    {
        return getenv('DSN');
    }
}
