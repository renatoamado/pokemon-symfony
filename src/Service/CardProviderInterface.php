<?php

namespace App\Service;

use Pokemon\Models\Card;
use Pokemon\Models\Model;

interface CardProviderInterface
{
    /**
     * @return array<int, Card>|null
     */
    public function findAll(): ?array;

    public function findById(string $id): ?Model;
}