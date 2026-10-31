<?php

declare(strict_types=1);

namespace App\Core;

final class Process
{
    public function run(): string|false|null
    {
        return shell_exec('true');
    }
}
