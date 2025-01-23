<?php

namespace Tests\Unit;

use App\Service\CardProvider;
use PHPUnit\Framework\TestCase;
use Pokemon\Models\Card;

class CardProviderTest extends TestCase
{
    private CardProvider $cardProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cardProvider = new CardProvider();
    }

    public function testFindAll(): void
    {
        $cards = $this->cardProvider->findAll();

        $this->assertIsArray($cards);
        $this->assertContainsOnlyInstancesOf(Card::class, $cards);
    }

    public function testFindOne(): void
    {
        $cardId = 'dp3-1';
        $card = $this->cardProvider->findById($cardId);

        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals($cardId, $card->getId());
    }
}