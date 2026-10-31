<?php

declare(strict_types=1);

namespace App\Core;

use App\Greeter;

final class ImportsShell
{
    public function greet(Greeter $greeter): string
    {
        return $greeter->hello();
    }
}
