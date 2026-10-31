<?php

declare(strict_types=1);

namespace App\Shell;

final class Filesystem
{
    public function read(): string|false
    {
        return file_get_contents('f');
    }
}
