<?php

namespace App\Exceptions;

use Exception;

class PokemonExceptions extends Exception
{
    public static function pokemonNotFound(string $pokemonId): self
    {
        $message = sprintf(
            'Pokémon "%s" not found.',
            $pokemonId
        );

        return new self(
            message: $message,
            code: 404
        );
    }

    public static function noPokemonNearby(): self
    {
        $message = 'No Pokémon nearby this time.';

        return new self(
            message: $message,
            code: 404
        );
    }
}
