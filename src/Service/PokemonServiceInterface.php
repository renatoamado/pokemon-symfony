<?php

declare(strict_types=1);

namespace App\Service;

use App\DataTransferObject\BaseDTO;
use App\Exceptions\PokemonExceptions;
use Psr\Cache\InvalidArgumentException;

interface PokemonServiceInterface
{
    /**
     * Get an array with all cards DTOs.
     *
     * @return array<int, BaseDTO>
     *
     * @throws InvalidArgumentException
     */
    public function getAllCards(): array;

    /**
     * Search for a card by id.
     *
     * @return BaseDTO|array<int, string>
     *
     * @throws InvalidArgumentException
     * @throws PokemonExceptions
     */
    public function findById(string $id): BaseDTO|array;
}
