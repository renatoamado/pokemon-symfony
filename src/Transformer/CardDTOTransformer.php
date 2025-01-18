<?php

namespace App\Transformer;

use App\DataTransferObject\CardDTO;
use Pokemon\Models\Card;
use Pokemon\Models\CardImages;
use Pokemon\Models\Model;
use ReflectionException;

class CardDTOTransformer
{
    /**
     * @throws ReflectionException
     */
    public function transform(Card $card): CardDTO
    {
        /** @var array<int, string> $types */
        $types = is_array($card->getTypes()) ? array_filter($card->getTypes(), 'is_string') : null;

        /** @var array<Model[]>|null $cardProperties */
        $cardProperties = [
            'resistances' => $card->getResistances(),
            'weaknesses' => $card->getWeaknesses(),
            'attacks' => $card->getAttacks(),
        ];

        return new CardDTO(
            id: $card->getId(),
            name: $card->getName(),
            types: $types,
            images: $this->parseModelToArray($card->getImages()),
            resistances: $this->parseModelToArray($cardProperties['resistances'] ?? null),
            weaknesses: $this->parseModelToArray($cardProperties['weaknesses'] ?? null),
            attacks: $this->parseModelToArray($cardProperties['attacks'] ?? null),
        );
    }

    /**
     * @param array<int, Card> $cards
     *
     * @return array<int, CardDTO>
     */
    public function transformCollection(array $cards): array
    {
        return array_map($this->transform(...), $cards);
    }

    /**
     * @param CardImages|array<Model>|null $models
     *
     * @return list<array<array<mixed>|int|string>>|null
     *
     * @throws ReflectionException
     */
    private function parseModelToArray(CardImages|array|null $models): ?array
    {
        if (null === $models) {
            return null;
        }

        if ($models instanceof CardImages) {
            return [array_filter($models->toArray(), 'is_string')];
        }

        $properties = [];
        foreach ($models as $model) {
            $properties[] = array_filter($model->toArray(), fn ($value): bool => is_string($value) || is_int($value) || is_array($value));
        }

        return $properties;
    }
}