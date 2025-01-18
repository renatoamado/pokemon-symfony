<?php

declare(strict_types=1);

namespace App\Service;

use App\DataTransferObject\BaseDTO;
use App\DataTransferObject\CardDTO;
use App\Exceptions\PokemonExceptions;
use Pokemon\Models\Card;
use Pokemon\Models\CardImages;
use Pokemon\Models\Model;
use Pokemon\Pokemon as TgcService;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class PokemonService implements PokemonServiceInterface
{
    private CacheItemPoolInterface $cache;

    private string $cacheKey;

    public function __construct(private readonly LoggerInterface $logger, private readonly TgcService $service)
    {
        $this->service::Options([
            'verify' => true,
            'timeout' => 20,
            'connection_timeout' => 5,
        ]);

        $this->service::ApiKey('c084f231-732a-46a8-9435-9dcf7ee75a29');

        $this->cache = new FilesystemAdapter();
        $this->cacheKey = 'all-cards';
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    public function getCacheService(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    public function setCacheService(CacheItemPoolInterface $cacheService): void
    {
        $this->cache = $cacheService;
    }

    /**
     * @return array<int, CardDTO>
     *
     * @throws InvalidArgumentException|ReflectionException
     */
    public function getAllCards(): array
    {
        $this->logger->debug('Get all cards');

        $cacheItem = $this->cache
            ->getItem($this->cacheKey)
            ->expiresAfter(60 * 60 * 24);

        if ($cacheItem->isHit()) {
            /** @var array<int, CardDTO> $cachedCards */
            $cachedCards = $cacheItem->get();

            return $cachedCards;
        } else {
            /** @var array<int, Card> $cards */
            $cards = $this->service::Card()->all();
            $collection = $this->getDtoCollection($cards);

            $cacheItem->set($collection);
            $this->cache->save($cacheItem);

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
        $cacheItem = $this->cache
            ->getItem("card-$id")
            ->expiresAfter(60 * 60 * 24);

        if ($cacheItem->isHit()) {
            /** @var CardDTO $cachedCards */
            $cachedCards = $cacheItem->get();

            return $cachedCards;
        } else {
            $card = $this->service::Card()->find($id);

            if (null === $card) {
                throw PokemonExceptions::pokemonNotFound($id);
            }

            $dto = $this->getDto($card);

            $cacheItem->set($dto);
            $this->cache->save($cacheItem);

            return $dto;
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getDto(Model $card): CardDTO
    {
        /** @var Card $card */
        /** @var array<int, string> $types */
        $types = is_array($card->getTypes()) ? array_filter($card->getTypes(), 'is_string') : null;

        /** @var array<CardImages>|null $images */
        $images = $card->getImages();

        /** @var array<Model[]>|null $cardProperties */
        $cardProperties = [
            'resistances' => $card->getResistances(),
            'weaknesses' => $card->getWeaknesses(),
            'attacks' => $card->getAttacks(),
        ];

        return new CardDTO(
            id: $card->getId(),
            name: $card->getName(),
            types: $types,
            images: $this->parseModelToArray($images),
            resistances: $this->parseModelToArray($cardProperties['resistances'] ?? null),
            weaknesses: $this->parseModelToArray($cardProperties['weaknesses'] ?? null),
            attacks: $this->parseModelToArray($cardProperties['attacks'] ?? null),
        );
    }

    /**
     * @param array<int, Card> $cards
     *
     * @return array<int, CardDTO>
     *
     * @throws ReflectionException
     */
    private function getDtoCollection(array $cards): array
    {
        $dtoArray = [];

        /** @var Card $card */
        foreach ($cards as $card) {
            /** @var array<int, string> $types */
            $types = is_array($card->getTypes()) ? array_filter($card->getTypes(), 'is_string') : null;

            /** @var array<CardImages>|null $images */
            $images = $card->getImages();

            /** @var array<Model[]>|null $cardProperties */
            $cardProperties = [
                'resistances' => $card->getResistances(),
                'weaknesses' => $card->getWeaknesses(),
                'attacks' => $card->getAttacks(),
            ];

            $dtoArray[] = new CardDTO(
                id: $card->getId(),
                name: $card->getName(),
                types: $types,
                images: $this->parseModelToArray($images),
                resistances: $this->parseModelToArray($cardProperties['resistances'] ?? null),
                weaknesses: $this->parseModelToArray($cardProperties['weaknesses'] ?? null),
                attacks: $this->parseModelToArray($cardProperties['attacks'] ?? null),
            );
        }

        return $dtoArray;
    }

    /**
     * @param CardImages|array<Model>|null $models
     *
     * @return list<array<array<mixed>|int|string>>|null
     *
     * @throws ReflectionException
     */
    private function parseModelToArray(CardImages|array|null $models): ?array
    {
        if (null === $models) {
            return null;
        }

        if ($models instanceof CardImages) {
            return [array_filter($models->toArray(), 'is_string')];
        }

        $properties = [];
        foreach ($models as $model) {
            $properties[] = array_filter($model->toArray(), fn ($value): bool => is_string($value) || is_int($value) || is_array($value));
        }

        return $properties;
    }
}
