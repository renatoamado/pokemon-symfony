<?php

namespace App\Service;

use Random\RandomException;

readonly class NonceGenerator implements NonceGeneratorInterface
{
    private string $nonce;

    /**
     * @throws RandomException
     */
    function __construct()
    {
        $this->nonce = base64_encode(random_bytes(16));
    }

    public function generate(): string
    {
        return $this->nonce;
    }
}