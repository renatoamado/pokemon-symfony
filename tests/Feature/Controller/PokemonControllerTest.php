<?php

namespace Tests\Feature\Controller;

use App\DataTransferObject\CardDTO;
use App\Service\CacheServiceInterface;
use App\Service\CardProviderInterface;
use App\Service\PokemonService;
use InvalidArgumentException;
use Mockery;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PokemonControllerTest extends WebTestCase
{
    private ?CacheServiceInterface $cacheService;

    private ?CardProviderInterface $cardProvider;

    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->cacheService = Mockery::mock(CacheServiceInterface::class);
        $this->cardProvider = Mockery::mock(CardProviderInterface::class);

        self::getContainer()->set(CacheServiceInterface::class, $this->cacheService);
        self::getContainer()->set(CardProviderInterface::class, $this->cardProvider);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cacheService = null;
        $this->cardProvider = null;

        Mockery::close();
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

        $this->client->request('GET', '/pokemon');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome Pokémon Trainer');
        self::assertSelectorExists('.card');
    }

    public function testShow(): void
    {
        $card = generateCard('123', 'pokemon 1');
        $cachedDTO = new CardDTO(
            id: $card->getId(),
            name: $card->getName(),
            images: [$card->getImages()->toArray()],
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

        $this->client->request('GET', '/pokemon/show/123');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'pokemon 1');
        self::assertSelectorExists('.card-details-info');
    }

    public function testShowNoCardFoundPage(): void
    {
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

        $this->client->request('GET', '/pokemon/show/123');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Card Not Found');
        self::assertSelectorTextContains('p', $cardID);
        self::assertSelectorExists('.not-found-message');
    }

    public function testShowAnyCardFoundPage(): void
    {
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

        $this->client->request('GET', '/pokemon');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Cards Not Found');
        self::assertSelectorExists('.not-found-message');
    }
}
