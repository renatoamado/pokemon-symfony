<?php

namespace Tests\Unit\Entity;

use App\Entity\Pokemon;
use PHPUnit\Framework\TestCase;

class PokemonEntityTest extends TestCase
{
    public function testCanSetAndGetName(): void
    {
        $pokemon = new Pokemon();
        $pokemon->setName('Pokemon');

        $this->assertSame('Pokemon', $pokemon->getName());
    }

    public function testIdNull(): void
    {
        $pokemon = new Pokemon();
        $this->assertNull($pokemon->getId());
    }
}
