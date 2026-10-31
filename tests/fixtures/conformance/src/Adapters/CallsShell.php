<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Greeter;

final class CallsShell
{
    public function greet(Greeter $greeter): string
    {
        return $greeter->hello();
    }
}
