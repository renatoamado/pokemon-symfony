<?php

namespace Tests\Unit\Repository;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Tests\Utils\DatabaseTestCase;

final class PokemonRepositoryTest extends DatabaseTestCase
{
    private PokemonRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(PokemonRepository::class);
    }

    public function testFindAll(): void
    {
        $pokemon = new Pokemon();
        $pokemon->setName('pikachu');
        $this->entityManager->persist($pokemon);
        $this->entityManager->flush();

        $pokemons = $this->repository->findOneByName('pikachu');

        $this->assertEquals('pikachu', $pokemons->getName());
    }
}
