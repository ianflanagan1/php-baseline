<?php

declare(strict_types=1);

use App\Adapters\SystemClock;
use App\Greeter;

echo (new Greeter)->hello(), (new SystemClock)->now(), getenv('HOME'), PHP_EOL;
