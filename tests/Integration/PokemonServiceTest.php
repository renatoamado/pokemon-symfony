<?php

namespace Tests\Integration;

use App\DataTransferObject\CardDTO;
use App\Exceptions\PokemonExceptions;
use App\Service\CacheServiceInterface;
use App\Service\CardProviderInterface;
use App\Service\PokemonService;
use App\Service\PokemonServiceInterface;
use App\Transformer\TransformerInterface;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class PokemonServiceTest extends TestCase
{
    private ?PokemonServiceInterface $pokemonService;

    private ?CardProviderInterface $cardProvider;

    private ?CacheServiceInterface $cacheService;

    private ?TransformerInterface $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheService = Mockery::mock(CacheServiceInterface::class);
        $this->transformer = Mockery::mock(TransformerInterface::class);
        $this->cardProvider = Mockery::mock(CardProviderInterface::class);
        $this->pokemonService = new PokemonService($this->cardProvider, $this->cacheService, $this->transformer);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cacheService = null;
        $this->pokemonService = null;
        $this->cardProvider = null;
        $this->transformer = null;

        Mockery::close();
    }

    public function testIfCacheMissesShouldReturnArrayOfCardDTO(): void
    {
        $cardOne = generateCard('ab-1', 'pokemon 1');
        $cardTwo = generateCard('ab-2', 'pokemon 2');

        $cachedDtoOne = new CardDTO(
            id: $cardOne->getId(),
            name: $cardOne->getName(),
        );

        $cachedDtoTwo = new CardDTO(
            id: $cardTwo->getId(),
            name: $cardTwo->getName(),
        );

        $cacheKey = 'all-cached-cards';

        $this->cacheService
            ->shouldReceive('getCacheKey')
            ->with(PokemonService::ALL_CARDS_CACHE_KEY)
            ->andReturn($cacheKey);
        $this->cacheService
            ->shouldReceive('getCacheItem')
            ->with($cacheKey)
            ->andReturn(null);

        $this->cardProvider
            ->shouldReceive('findAll')
            ->andReturn([$cardOne, $cardTwo]);

        $this->transformer
            ->shouldReceive('transformCollection')
            ->with([$cardOne, $cardTwo])
            ->andReturn([$cachedDtoOne, $cachedDtoTwo]);

        $this->cacheService
            ->shouldReceive('setCacheKey')
            ->with($cacheKey, [$cachedDtoOne, $cachedDtoTwo])
            ->once();

        $cards = $this->pokemonService->findAll();

        $this->assertIsArray($cards);
        $this->assertIsArray($cards[0]->toArray());
        $this->assertCount(2, $cards);
        $this->assertInstanceOf(CardDTO::class, $cards[0]);
        $this->assertEquals($cardOne->getName(), $cards[0]->getName());
        $this->assertEquals($cardTwo->getName(), $cards[1]->getName());
    }

    public function testIfCacheHitsShouldReturnArrayOfCardDTO(): void
    {
        $cardOne = generateCard('ab-1', 'pokemon 1');
        $cardTwo = generateCard('ab-2', 'pokemon 2');

        $cachedDtoOne = new CardDTO(
            id: $cardOne->getId(),
            name: $cardOne->getName(),
        );

        $cachedDtoTwo = new CardDTO(
            id: $cardTwo->getId(),
            name: $cardTwo->getName(),
        );

        $cacheKey = 'all-cached-cards';

        $this->cacheService
            ->shouldReceive('getCacheKey')
            ->with(PokemonService::ALL_CARDS_CACHE_KEY)
            ->andReturn($cacheKey);
        $this->cacheService
            ->shouldReceive('getCacheItem')
            ->with($cacheKey)
            ->andReturn([$cachedDtoOne, $cachedDtoTwo]);

        $this->cardProvider
            ->shouldReceive('findAll')
            ->andReturn([$cardOne, $cardTwo]);

        $this->transformer
            ->shouldReceive('transformCollection')
            ->with([$cardOne, $cardTwo])
            ->andReturn([$cachedDtoOne, $cachedDtoTwo]);

        $response = $this->pokemonService->findAll();

        $this->assertIsArray($response);
    }

    public function testThrowExceptionIfNotFindAnyPokemon(): void
    {
        $this->expectException(PokemonExceptions::class);

        $cacheKey = 'all-cached-cards';

        $this->cacheService
            ->shouldReceive('getCacheKey')
            ->with(PokemonService::ALL_CARDS_CACHE_KEY)
            ->andReturn($cacheKey);
        $this->cacheService
            ->shouldReceive('getCacheItem')
            ->with($cacheKey)
            ->andReturn(null);

        $this->cardProvider
            ->shouldReceive('findAll')
            ->andReturn(null);

        $this->pokemonService->findAll();
    }

    public function testFindByIdCacheHitShouldReturnCardDTO(): void
    {
        $card = generateCard('123', 'pokemon 1');

        $cachedDTO = new CardDTO(
            id: $card->getId(),
            name: $card->getName(),
        );

        $cardID = '123';
        $cacheKey = "card-$cardID";

        $this->cacheService
            ->shouldReceive('getCacheKey')
            ->with(PokemonService::CARD_CACHE_KEY_PREFIX, $cardID)
            ->andReturn($cacheKey);
        $this->cacheService
            ->shouldReceive('getCacheItem')
            ->with($cacheKey)
            ->andReturn($cachedDTO);

        $this->cardProvider
            ->shouldReceive('findById')
            ->with($cardID)
            ->andReturn($card);

        $response = $this->pokemonService->findById($cardID);
        $this->assertEquals($cachedDTO, $response);
    }

    public function testFindByIdCacheMissShouldReturnCardDTO(): void
    {
        $card = generateCard('123', 'pokemon 1');

        $cachedDTO = new CardDTO(
            id: $card->getId(),
            name: $card->getName(),
            types: $card->getTypes(),
            images: $card->getImages()?->toArray(),
            resistances: $card->getResistances(),
            weaknesses: $card->getWeaknesses(),
            attacks: $card->getAttacks(),
        );

        $cardID = '123';
        $cacheKey = "card-$cardID";

        $this->cacheService
            ->shouldReceive('getCacheKey')
            ->with(PokemonService::CARD_CACHE_KEY_PREFIX, $cardID)
            ->andReturn($cacheKey);
        $this->cacheService
            ->shouldReceive('getCacheItem')
            ->with($cacheKey)
            ->andReturn(null);

        $this->cardProvider
            ->shouldReceive('findById')
            ->with($cardID)
            ->andReturn($card);

        $this->transformer
            ->shouldReceive('transform')
            ->with($card)
            ->andReturn($cachedDTO);

        $this->cacheService
            ->shouldReceive('setCacheKey')
            ->with($cacheKey, $cachedDTO)
            ->once();

        $response = $this->pokemonService->findById($cardID);

        $this->assertInstanceOf(CardDTO::class, $response);
        $this->assertEquals($card->getId(), $response->getId());
        $this->assertEquals($card->getName(), $response->getName());
        $this->assertEquals($card->getTypes(), $response->getTypes());
        $this->assertEquals($card->getImages()->toArray(), $response->getImages());
        $this->assertEquals($card->getResistances()[0]->toArray(), $response->getResistances()[0]->toArray());
        $this->assertEquals($card->getWeaknesses()[0]->toArray(), $response->getWeaknesses()[0]->toArray());
        $this->assertEquals($card->getAttacks()[0]->toArray(), $response->getAttacks()[0]->toArray());
    }

    public function testThrowExceptionIfNoPokemonIsFound(): void
    {
        $this->expectException(PokemonExceptions::class);

        $cardID = '123';
        $cacheKey = "card-$cardID";

        $this->cacheService
            ->shouldReceive('getCacheKey')
            ->with(PokemonService::CARD_CACHE_KEY_PREFIX, $cardID)
            ->andReturn($cacheKey);
        $this->cacheService
            ->shouldReceive('getCacheItem')
            ->with($cacheKey)
            ->andReturn(null);

        $this->cardProvider
            ->shouldReceive('findById')
            ->with($cardID)
            ->andThrow(InvalidArgumentException::class);

        $this->pokemonService->findById($cardID);
    }
}