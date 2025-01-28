<?php

namespace App\EventListener;

use App\Service\NonceGenerator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final readonly class NonceSubscriber
{
    private string $nonce;

    public function __construct(private NonceGenerator $nonceGenerator, private Environment $twig)
    {
        $this->nonce = $this->nonceGenerator->generate();
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $cspHeader = "default-src 'self'; script-src 'self' 'nonce-{$this->nonce}' 'strict-dynamic'; style-src 'self'; img-src 'self' data: https://images.pokemontcg.io; font-src 'self'; frame-ancestors 'self'; form-action 'self';";
        $response->headers->set('Content-Security-Policy', $cspHeader);
    }

    #[AsEventListener(event: KernelEvents::CONTROLLER)]
    public function onKernelController(ControllerEvent $event): void
    {
        $this->twig->addGlobal('nonce', $this->nonce);
    }
}
