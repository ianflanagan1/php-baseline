<?php

declare(strict_types=1);

namespace App\Shell;

final class Randomness
{
    public function roll(): int
    {
        return random_int(1, 6);
    }
}
