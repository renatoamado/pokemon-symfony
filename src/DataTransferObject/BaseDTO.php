<?php

declare(strict_types=1);

namespace App\DataTransferObject;

abstract class BaseDTO
{
    /**
     * @return array<string, array<array<string>|string>|string|null>
     */
    abstract public function toArray(): array;
}
