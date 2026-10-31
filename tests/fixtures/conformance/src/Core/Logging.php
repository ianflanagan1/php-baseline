<?php

declare(strict_types=1);

namespace App\Core;

final class Logging
{
    public function log(): bool
    {
        return error_log('hello');
    }
}
