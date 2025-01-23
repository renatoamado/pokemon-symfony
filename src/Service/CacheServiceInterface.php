<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

interface CacheServiceInterface
{
    public function getCacheKey(string $prefix, string $identifier = ''): string;

    public function setCacheKey(string $key, mixed $value, int $ttl = 86400): void;

    public function getCacheItem(string $key): mixed;

    public function getCache(): CacheItemPoolInterface;
}