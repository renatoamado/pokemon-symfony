<?php

namespace Tests\Unit;

use App\DataTransferObject\CardDTO;
use App\Transformer\Transformer;
use App\Transformer\TransformerInterface;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    private TransformerInterface $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new Transformer();
    }

    public function testTransformCardToDTO(): void
    {
        $card = generateCard('123', 'pokemon 1');

        $dto = $this->transformer->transform($card);

        $this->assertInstanceOf(CardDTO::class, $dto);
        $this->assertEquals('123', $dto->getId());
        $this->assertEquals('pokemon 1', $dto->getName());
    }

    public function testTransformCollection(): void
    {
        $card1 = generateCard('123', 'pokemon 1');
        $card2 = generateCard('321', 'pokemon 2');

        $dtos = $this->transformer->transformCollection([$card1, $card2]);

        $this->assertCount(2, $dtos);
        $this->assertInstanceOf(CardDTO::class, $dtos[0]);
        $this->assertInstanceOf(CardDTO::class, $dtos[1]);
    }

    public function testTransformNullModel(): void
    {
        $card = generateCard('123', 'pokemon 1');
        $card->setImages(null);

        $dto = $this->transformer->transform($card);

        $this->assertInstanceOf(CardDTO::class, $dto);
        $this->assertEquals('123', $dto->getId());
        $this->assertEquals('pokemon 1', $dto->getName());
        $this->assertNull($dto->getImages());
    }
}
