<?php

namespace Tests\Integration;

use App\DataTransferObject\CardDTO;
use App\Exceptions\PokemonExceptions;
use App\Service\PokemonService;
use Mockery;
use PHPUnit\Framework\TestCase;
use Pokemon\Pokemon;
use Pokemon\Resources\Interfaces\QueriableResourceInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class PokemonServiceTest extends TestCase
{
    private ?PokemonService $pokemonService;

    private ?QueriableResourceInterface $resource;

    private ?Pokemon $pokemon;

    private ?ArrayAdapter $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $logger = new NullLogger();
        $this->cache = new ArrayAdapter();

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
            ->with('c084f231-732a-46a8-9435-9dcf7ee75a29');

        $this->pokemonService = new PokemonService($logger, $this->pokemon);
        $this->pokemonService->setCacheService($this->cache);
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

        $cards = $this->pokemonService->getAllCards();

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

        $cacheItem = $this->cache->getItem('all-cached-cards');
        $cacheItem->set([$cachedDtoOne, $cachedDtoTwo]);

        $this->cache->save($cacheItem);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $response = $this->pokemonService->getAllCards();

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

        $cacheItem = $this->cache->getItem('card-123');
        $cacheItem->set($cachedDTO);

        $this->cache->save($cacheItem);

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
            ->andReturn(null);

        $this->pokemonService->findById('ab-2');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->pokemonService->getCacheService()->clear();
        $this->pokemonService = null;
        $this->resource = null;
        $this->pokemon = null;

        Mockery::close();
    }
}