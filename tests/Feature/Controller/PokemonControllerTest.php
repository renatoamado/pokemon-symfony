<?php

namespace Tests\Feature\Controller;

use App\DataTransferObject\CardDTO;
use App\Service\CacheServiceInterface;
use App\Service\PokemonService;
use App\Service\PokemonServiceInterface;
use App\Transformer\CardDTOTransformer;
use Mockery;
use Pokemon\Pokemon;
use Pokemon\Resources\Interfaces\QueriableResourceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PokemonControllerTest extends WebTestCase
{
    private ?PokemonService $pokemonService = null;

    private ?CacheServiceInterface $cache;

    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = PokemonControllerTest::createClient();

        $this->cache = $this->getContainer()->get(CacheServiceInterface::class);
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

        $cacheItem = $this->cache->getCache()->getItem('all-cached-cards');
        $cacheItem->set([$cachedDtoOne, $cachedDtoTwo]);

        $this->cache->getCache()->save($cacheItem);

        $this->pokemonService = $this->getContainer()->get(PokemonServiceInterface::class);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $this->client->request('GET', '/pokemon');

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

        $cacheItem = $this->cache->getCache()->getItem('card-ab-1');
        $cacheItem->set($cachedDTO);

        $this->cache->getCache()->save($cacheItem);

        $this->pokemonService = $this->getContainer()->get(PokemonServiceInterface::class);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $this->client->request('GET', '/pokemon/show/ab-1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'pokemon 1');
        self::assertSelectorExists('.card-details-info');
    }

    public function testShowNoCardFoundPage(): void
    {
        $cardOne = generateCard('ab-1', 'pokemon 1');
        $cachedDTO = new CardDTO(
            id: $cardOne->getId(),
            name: $cardOne->getName(),
            images: [$cardOne->getImages()->toArray()],
        );

        $cacheItem = $this->cache->getCache()->getItem('card-not-found');
        $cacheItem->set($cachedDTO);

        $this->cache->getCache()->save($cacheItem);

        $this->pokemonService = $this->getContainer()->get(PokemonServiceInterface::class);
        $this->pokemonService->setCacheKey($cacheItem->getKey());

        $this->client->request('GET', '/pokemon/show/ab-1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Card Not Found');
        self::assertSelectorTextContains('p', $cachedDTO->getId());
        self::assertSelectorExists('.not-found-message');
    }

    public function testShowAnyCardFoundPage(): void
    {
        $resource = Mockery::mock(QueriableResourceInterface::class);
        $pokemon = Mockery::mock(Pokemon::class);

        $pokemon
            ->shouldReceive('Options')
            ->with([
                'verify' => true,
                'timeout' => 20,
                'connection_timeout' => 5,
            ]);
        $pokemon
            ->shouldReceive('ApiKey')
            ->with(null);

        $pokemon
            ->shouldReceive('Card')
            ->andReturn($resource);

        $resource
            ->shouldReceive('all')
            ->andReturn(null);

        $service = new PokemonService($pokemon, $this->cache, new CardDTOTransformer());

        static::getContainer()->set(PokemonService::class, $service);

        $this->client->request('GET', '/pokemon');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Cards Not Found');
        self::assertSelectorExists('.not-found-message');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cache->getCache()->clear();
        $this->pokemonService = null;
        $this->cache = null;

        Mockery::close();
    }
}
