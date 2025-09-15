<?php

declare(strict_types=1);

namespace App\Factory;

use GuzzleHttp\Client;

final readonly class BinPackingClientFactory
{
    public static function create(string $baseUri): Client
    {
        return new Client([
            'base_uri' => $baseUri,
        ]);
    }
}
