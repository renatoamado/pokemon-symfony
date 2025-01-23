<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

readonly class CacheService implements CacheServiceInterface
{
    function __construct(private CacheItemPoolInterface $cache)
    {
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }
}