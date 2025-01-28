<?php

namespace App\Service;

interface NonceGeneratorInterface
{
    public function generate(): string;
}