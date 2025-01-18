<?php

declare(strict_types=1);

namespace App\DataTransferObject;

final class CardDTO extends BaseDTO
{
    /**
     * @param array<int, string>|null                   $types
     * @param list<array<array<mixed>|int|string>>|null $images
     * @param list<array<array<mixed>|int|string>>|null $resistances
     * @param list<array<array<mixed>|int|string>>|null $weaknesses
     * @param list<array<array<mixed>|int|string>>|null $attacks
     */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly ?array $types = null,
        private readonly ?array $images = null,
        private readonly ?array $resistances = null,
        private readonly ?array $weaknesses = null,
        private readonly ?array $attacks = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, string>|null
     */
    public function getTypes(): ?array
    {
        return $this->types;
    }

    /**
     * @return list<array<array<mixed>|int|string>>|null
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * @return list<array<array<mixed>|int|string>>|null
     */
    public function getResistances(): ?array
    {
        return $this->resistances;
    }

    /**
     * @return list<array<array<mixed>|int|string>>|null
     */
    public function getWeaknesses(): ?array
    {
        return $this->weaknesses;
    }

    /**
     * @return list<array<array<mixed>|int|string>>|null
     */
    public function getAttacks(): ?array
    {
        return $this->attacks;
    }

    /**
     * @return array<string, array<int, array<array<mixed>|int|string>|string>|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'types' => $this->types,
            'images' => $this->images,
            'resistances' => $this->resistances,
            'weaknesses' => $this->weaknesses,
            'attacks' => $this->attacks,
        ];
    }
}
