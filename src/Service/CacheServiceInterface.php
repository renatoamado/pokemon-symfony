<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

interface CacheServiceInterface
{
    public function getCache(): CacheItemPoolInterface;
}