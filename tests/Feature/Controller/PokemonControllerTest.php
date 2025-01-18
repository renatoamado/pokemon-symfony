<?php

namespace Tests\Feature\Controller;

use App\DataTransferObject\CardDTO;
use App\Service\PokemonService;
use App\Service\PokemonServiceInterface;
use Mockery;
use Pagerfanta\Adapter\AdapterInterface;
use Pokemon\Pokemon;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PokemonControllerTest extends WebTestCase
{
    private ?PokemonService $pokemonService;

    private ?Pokemon $pokemon;

    private ?ArrayAdapter $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $logger = new NullLogger();
        $this->cache = new ArrayAdapter();

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

    public function testIndex(): void
    {
        $cardOne = generateCard('ab-1', 'pokemon 1');
        $cardTwo = generateCard('ab-2', 'pokemon 2');

        $cachedDtoOne = new CardDTO(
            id: $cardOne->getId(),
            name: $cardOne->getName(),
            images: [$cardOne->getImages()->toArray()],
        );

        $cachedDtoTwo = new CardDTO(
            id: $cardTwo->getId(),
            name: $cardTwo->getName(),
            images: [$cardTwo->getImages()->toArray()],
        );

        $cacheItem = $this->cache->getItem('all-cached-cards');
        $cacheItem->set([$cachedDtoOne, $cachedDtoTwo]);

        $this->cache->save($cacheItem);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $client = static::createClient();
        static::getContainer()->set(AdapterInterface::class, $this->cache);
        static::getContainer()->set(PokemonServiceInterface::class, $this->pokemonService);

        $client->request('GET', '/pokemon');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome Pokémon Trainer');
        self::assertSelectorExists('.card');
    }

    public function testShow(): void
    {
        $cardOne = generateCard('ab-1', 'pokemon 1');
        $cachedDTO = new CardDTO(
            id: $cardOne->getId(),
            name: $cardOne->getName(),
            images: [$cardOne->getImages()->toArray()],
        );

        $cacheItem = $this->cache->getItem('card-ab-1');
        $cacheItem->set($cachedDTO);

        $this->cache->save($cacheItem);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $client = static::createClient();
        static::getContainer()->set(AdapterInterface::class, $this->cache);
        static::getContainer()->set(PokemonServiceInterface::class, $this->pokemonService);

        $client->request('GET', '/pokemon/show/ab-1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'pokemon 1');
        self::assertSelectorExists('.card-details-info');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->pokemonService->getCacheService()->clear();
        $this->pokemonService = null;
        $this->pokemon = null;

        Mockery::close();
    }
}
