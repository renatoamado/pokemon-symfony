<?php

namespace Tests\Unit;

use App\Service\CacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;

    public function setUp(): void
    {
        parent::setUp();
        $arrayAdapter = new ArrayAdapter();
        $this->cacheService = new CacheService($arrayAdapter);
    }

    public function testSetAndGetCacheItem(): void
    {
        $key = 'test-key';
        $value = 'test-value';

        $this->cacheService->setCacheKey($key, $value);
        $cachedValue = $this->cacheService->getCacheItem($key);

        $this->assertEquals($value, $cachedValue);
        $this->assertEquals($key, $this->cacheService->getCacheKey($key));
    }

    public function testGetCacheItemReturnNullWhenKeyDoesNotExists(): void
    {
        $key = 'test-key';
        $cachedValue = $this->cacheService->getCacheItem($key);

        $this->assertNull($cachedValue);
    }
}