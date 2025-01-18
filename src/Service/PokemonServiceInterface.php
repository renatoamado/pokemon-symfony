<?php

declare(strict_types=1);

namespace App\Service;

use App\DataTransferObject\BaseDTO;
use App\Exceptions\PokemonExceptions;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

interface PokemonServiceInterface
{
    /**
     * Return the cache service.
     */
    public function getCacheService(): CacheItemPoolInterface;

    public function setCacheService(CacheItemPoolInterface $cacheService): void;

    /**
     * Get an array with all cards DTOs.
     *
     * @return array<int, BaseDTO>
     *
     * @throws InvalidArgumentException
     */
    public function getAllCards(): array;

    /**
     * Search for a card by id.
     *
     * @return BaseDTO|array<int, string>
     *
     * @throws InvalidArgumentException
     * @throws PokemonExceptions
     */
    public function findById(string $id): BaseDTO|array;
}
