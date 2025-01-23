<?php

namespace Tests\Integration;

use App\DataTransferObject\CardDTO;
use App\Exceptions\PokemonExceptions;
use App\Service\CacheService;
use App\Service\CacheServiceInterface;
use App\Service\PokemonService;
use App\Transformer\CardDTOTransformer;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Pokemon\Pokemon;
use Pokemon\Resources\Interfaces\QueriableResourceInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class PokemonServiceTest extends TestCase
{
    private ?PokemonService $pokemonService;

    private ?QueriableResourceInterface $resource;

    private ?Pokemon $pokemon;

    private ?CacheServiceInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $adapter = new ArrayAdapter();
        $this->cache = new CacheService($adapter);

        $this->resource = Mockery::mock(QueriableResourceInterface::class);
        $this->pokemon = Mockery::mock(Pokemon::class);

        $this->pokemon
            ->shouldReceive('Options')
            ->with([
                'verify' => true,
                'timeout' => 20,
                'connection_timeout' => 5,
            ]);
        $this->pokemon
            ->shouldReceive('ApiKey')
            ->with(null);

        $this->pokemonService = new PokemonService($this->pokemon, $this->cache, new CardDTOTransformer());
    }

    public function testIfCacheMissesShouldReturnArrayOfCardDTO(): void
    {
        $cardOne = generateCard('ab-1', 'pokemon 1');
        $cardTwo = generateCard('ab-2', 'pokemon 2');

        $this->pokemon
            ->shouldReceive('Card')
            ->andReturn($this->resource);

        $this->resource
            ->shouldReceive('all')
            ->andReturn([$cardOne, $cardTwo]);

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

        $cacheItem = $this->cache->getCache()->getItem('all-cached-cards');
        $cacheItem->set([$cachedDtoOne, $cachedDtoTwo]);

        $this->cache->getCache()->save($cacheItem);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $response = $this->pokemonService->findAll();

        $this->assertIsArray($response);
        $this->assertEquals($this->pokemonService->getCacheKey(), $cacheItem->getKey());
    }

    public function testFindByIdCacheHitShouldReturnCardDTO(): void
    {
        $card = generateCard('123', 'pokemon 1');
        $cachedDTO = new CardDTO(
            id: $card->getId(),
            name: $card->getName(),
        );

        $cacheItem = $this->cache->getCache()->getItem('card-123');
        $cacheItem->set($cachedDTO);

        $this->cache->getCache()->save($cacheItem);

        $response = $this->pokemonService->findById('123');
        $this->assertEquals($cachedDTO, $response);
    }

    public function testFindByIdCacheMissShouldReturnCardDTO(): void
    {
        $card = generateCard('123', 'pokemon 1');

        $this->pokemon
            ->shouldReceive('Card')
            ->andReturn($this->resource);

        $this->resource
            ->shouldReceive('find')
            ->with($card->getId())
            ->andReturn($card);

        $response = $this->pokemonService->findById($card->getId());

        $this->assertInstanceOf(CardDTO::class, $response);
        $this->assertEquals($card->getId(), $response->getId());
        $this->assertEquals($card->getName(), $response->getName());
        $this->assertEquals($card->getTypes(), $response->getTypes());
        $this->assertEquals($card->getImages()->toArray(), $response->getImages()[0]);
        $this->assertEquals($card->getResistances()[0]->toArray(), $response->getResistances()[0]);
        $this->assertEquals($card->getWeaknesses()[0]->toArray(), $response->getWeaknesses()[0]);
        $this->assertEquals($card->getAttacks()[0]->toArray(), $response->getAttacks()[0]);
    }

    public function testNullModelParsingShouldReturnNull(): void
    {
        $card = generateCard('ab-1', 'pokemon 1');
        $card->setImages(null);

        $this->pokemon
            ->shouldReceive('Card')
            ->andReturn($this->resource);

        $this->resource
            ->shouldReceive('find')
            ->with($card->getId())
            ->andReturn($card);

        $response = $this->pokemonService->findById($card->getId());

        $this->assertInstanceOf(CardDTO::class, $response);
        $this->assertNull($response->getImages());
    }

    public function testThrowExceptionIfNoPokemonIsFound(): void
    {
        $this->expectException(PokemonExceptions::class);

        $this->pokemon
            ->shouldReceive('Card')
            ->andReturn($this->resource);

        $this->resource
            ->shouldReceive('find')
            ->with('ab-2')
            ->andThrow(InvalidArgumentException::class);

        $this->pokemonService->findById('ab-2');
    }

    public function testThrowExceptionIfNotFindAnyPokemon(): void
    {
        $this->expectException(PokemonExceptions::class);

        $this->pokemon
            ->shouldReceive('Card')
            ->andReturn($this->resource);

        $this->resource
            ->shouldReceive('all')
            ->andReturn(null);

        $this->pokemonService->findAll();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cache->getCache()->clear();
        $this->pokemonService = null;
        $this->resource = null;
        $this->pokemon = null;

        Mockery::close();
    }
}