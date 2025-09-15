<?php

namespace App\Factory;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class CacheFactory
{
    public static function create(): CacheInterface
    {
        return new ArrayAdapter();
    }
}
