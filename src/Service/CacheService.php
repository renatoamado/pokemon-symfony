<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

readonly class CacheService implements CacheServiceInterface
{
    private const DEFAULT_TTL = 86400;

    function __construct(private CacheItemPoolInterface $cache)
    {
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    public function getCacheKey(string $prefix, string $identifier = ''): string
    {
        return $identifier ? "$prefix-$identifier" : $prefix;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setCacheKey(string $key, mixed $value, int $ttl = self::DEFAULT_TTL): void
    {
        $cache = $this->getCache();
        $cacheItem = $cache
            ->getItem($key)
            ->expiresAfter($ttl)
            ->set($value);

        $cache->save($cacheItem);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCacheItem(string $key): mixed
    {
        $cache = $this->getCache();
        $cacheItem = $cache->getItem($key);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        return null;
    }
}