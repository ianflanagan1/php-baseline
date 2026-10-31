<?php

declare(strict_types=1);

namespace App\Core;

final class Randomness
{
    public function roll(): int
    {
        return random_int(1, 6);
    }
}
