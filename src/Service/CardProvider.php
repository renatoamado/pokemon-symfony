<?php

namespace App\Service;

use Pokemon\Models\Card;
use Pokemon\Models\Model;
use Pokemon\Pokemon as TgcService;

class CardProvider implements CardProviderInterface
{
    /**
     * @return array<int, Card>|null
     */
    public function findAll(): ?array
    {
        /** @var array<int, Card> $cards */
        $cards = TgcService::Card()->all();

        return empty($cards) ? null : $cards;
    }

    public function findById(string $id): ?Model
    {
        return TgcService::Card()->find($id);
    }
}