<?php

declare(strict_types=1);

namespace App\Core;

final class Network
{
    public function connect(): \PDO
    {
        return new \PDO('sqlite::memory:');
    }

    public function fetch(): \CurlHandle|false
    {
        return curl_init('https://example.com');
    }
}
