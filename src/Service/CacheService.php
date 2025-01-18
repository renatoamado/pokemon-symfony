<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

readonly class CacheService implements CacheServiceInterface
{
    private CacheItemPoolInterface $cache;

    function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }
}