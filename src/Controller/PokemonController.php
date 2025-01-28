<?php

namespace App\Controller;

use App\Service\PokemonService;
use Exception;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokemonController extends AbstractController
{
    private string $nonce;

    /**
     * @throws RandomException
     */
    function __construct(private readonly PokemonService $service)
    {
        $this->nonce = base64_encode(random_bytes(16));
    }

    #[Route('/pokemon', name: 'pokemon_list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        try {
            $cards = $this->service->findAll();

            $adapter = new ArrayAdapter($cards);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(20);
            $pagerfanta->setCurrentPage((int)$request->query->get('page', 1));

            $response = $this->render('pokemon/index.html.twig', [
                'cards' => $pagerfanta->getCurrentPageResults(),
                'pager' => $pagerfanta,
                'error' => null,
                'nonce' => $this->nonce,
            ]);
        } catch (Exception $exception) {
            $response = $this->render('pokemon/index.html.twig', [
                'error' => $exception->getMessage(),
                'nonce' => $this->nonce,
            ]);
        }

        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'nonce-{$this->nonce}' 'strict-dynamic'; style-src 'self'; img-src 'self' data: https://images.pokemontcg.io; font-src 'self'; frame-ancestors 'self'; form-action 'self';");

        return $response;
    }

    #[Route('/pokemon/show/{id}', name: 'pokemon_profile', methods: ['GET'])]
    public function show(string $id): Response
    {
        try {
            $card = $this->service->findById($id);

            $response = $this->render('pokemon/show.html.twig', [
                'card' => $card,
                'error' => null,
                'nonce' => $this->nonce,
            ]);
        } catch (Exception $exception) {
            $response = $this->render('pokemon/show.html.twig', [
                'error' => $exception->getMessage(),
                'nonce' => $this->nonce,
            ]);
        }

        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'nonce-{$this->nonce}' 'strict-dynamic'; style-src 'self'; img-src 'self' data: https://images.pokemontcg.io; font-src 'self'; frame-ancestors 'self'; form-action 'self';");

        return $response;
    }
}
