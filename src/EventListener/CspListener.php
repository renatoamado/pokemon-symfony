<?php

namespace App\EventListener;

use App\Service\NonceGenerator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class CspListener
{
    public function __construct(private NonceGenerator $nonceGenerator)
    {
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $nonce = $this->nonceGenerator->generate();

        $cspHeader = "default-src 'self'; script-src 'self' 'nonce-{$nonce}' 'strict-dynamic'; style-src 'self'; img-src 'self' data: https://images.pokemontcg.io; font-src 'self'; frame-ancestors 'self'; form-action 'self';";

        $response->headers->set('Content-Security-Policy', $cspHeader);
    }
}
