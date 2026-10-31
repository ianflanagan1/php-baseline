<?php

declare(strict_types=1);

namespace App\Core;

final class GlobalState
{
    public function count(): int
    {
        global $count;

        return ++$count;
    }
}
