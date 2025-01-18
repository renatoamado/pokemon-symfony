<?php

declare(strict_types=1);

namespace App\Service;

use App\DataTransferObject\BaseDTO;
use App\DataTransferObject\CardDTO;
use App\Exceptions\PokemonExceptions;
use App\Transformer\CardDTOTransformer;
use Pokemon\Models\Card;
use Pokemon\Pokemon as TgcService;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;

final class PokemonService implements PokemonServiceInterface
{
    private string $cacheKey = 'all-cards';

    public function __construct(
        private readonly TgcService $service,
        private readonly CacheServiceInterface $cacheService,
        private readonly CardDTOTransformer $transformer,
    ) {
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @return array<int, CardDTO>
     *
     * @throws InvalidArgumentException
     * @throws PokemonExceptions
     */
    public function getAllCards(): array
    {
        $cache = $this->cacheService->getCache();

        $cacheItem = $cache
            ->getItem($this->cacheKey)
            ->expiresAfter(60 * 60 * 24);

        if ($cacheItem->isHit()) {
            /** @var array<int, CardDTO> $cachedCards */
            $cachedCards = $cacheItem->get();

            return $cachedCards;
        } else {
            /** @var array<int, Card> $cards */
            $cards = $this->service::Card()->all();
            
            if (!$cards) {
                throw PokemonExceptions::noPokemonsNearby();
            }

            $collection = $this->transformer->transformCollection($cards);

            $cacheItem->set($collection);
            $cache->save($cacheItem);

            return $collection;
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws PokemonExceptions
     */
    public function findById(string $id): BaseDTO
    {
        $cache = $this->cacheService->getCache();

        $cacheItem = $cache
            ->getItem("card-$id")
            ->expiresAfter(60 * 60 * 24);

        if ($cacheItem->isHit()) {
            /** @var CardDTO $cachedCards */
            $cachedCards = $cacheItem->get();

            return $cachedCards;
        } else {
            try {
                $card = $this->service::Card()->find($id);
            } catch (\InvalidArgumentException) {
                throw PokemonExceptions::pokemonNotFound($id);
            }

            /** @var Card $card */
            $dto = $this->transformer->transform($card);

            $cacheItem->set($dto);
            $cache->save($cacheItem);

            return $dto;
        }
    }
}
