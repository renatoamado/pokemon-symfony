<?php

declare(strict_types=1);

namespace App\Service;

use App\DataTransferObject\BaseDTO;
use App\DataTransferObject\CardDTO;
use App\Exceptions\PokemonExceptions;
use App\Transformer\TransformerInterface;
use InvalidArgumentException;
use Pokemon\Models\Card;

class PokemonService implements PokemonServiceInterface
{
    public const ALL_CARDS_CACHE_KEY = 'all-cards';

    public const CARD_CACHE_KEY_PREFIX = 'card';

    public function __construct(
        private readonly CardProviderInterface $cardProvider,
        private readonly CacheServiceInterface $cacheService,
        private readonly TransformerInterface $transformer,
    ) {
    }

    /**
     * @return array<int, BaseDTO>
     *
     * @throws PokemonExceptions
     */
    public function findAll(): array
    {
        $cacheKey = $this->cacheService->getCacheKey(self::ALL_CARDS_CACHE_KEY);

        /** @var array<int, CardDTO>|null $cachedCards */
        $cachedCards = $this->cacheService->getCacheItem($cacheKey);

        if (null !== $cachedCards) {
            return $cachedCards;
        }
        
        /** @var array<int, Card> $cards */
        $cards = $this->cardProvider->findAll();

        if (!$cards) {
            throw PokemonExceptions::noPokemonNearby();
        }

        $collection = $this->transformer->transformCollection($cards);
        $this->cacheService->setCacheKey($cacheKey, $collection);

        return $collection;
    }

    /**
     * @throws PokemonExceptions
     */
    public function findById(string $id): BaseDTO
    {
        $cacheKey = $this->cacheService->getCacheKey(self::CARD_CACHE_KEY_PREFIX, $id);

        /** @var CardDTO|null $cachedCard */
        $cachedCard = $this->cacheService->getCacheItem($cacheKey);

        if (null !== $cachedCard) {
            return $cachedCard;
        }

        try {
            $card = $this->cardProvider->findById($id);
        } catch (InvalidArgumentException) {
            throw PokemonExceptions::pokemonNotFound($id);
        }

        /** @var Card $card */
        $dto = $this->transformer->transform($card);
        $this->cacheService->setCacheKey($cacheKey, $dto);

        return $dto;
    }
}
