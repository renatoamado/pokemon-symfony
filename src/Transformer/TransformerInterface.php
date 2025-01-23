<?php

namespace App\Transformer;

use App\DataTransferObject\BaseDTO;
use Pokemon\Models\Card;

interface TransformerInterface
{
    public function transform(Card $card): BaseDTO;

    /**
     * @param array<int, Card> $cards
     *
     * @return array<int, BaseDTO>
     */
    public function transformCollection(array $cards): array;
}