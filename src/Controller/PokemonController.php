<?php

namespace App\Controller;

use App\Service\PokemonService;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokemonController extends AbstractController
{
    function __construct(private readonly PokemonService $service)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/pokemon', name: 'pokemon_list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $cards = $this->service->getAllCards();

        $adapter = new ArrayAdapter($cards);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage((int) $request->query->get('page', 1));

        return $this->render('pokemon/index.html.twig', [
            'cards' => $pagerfanta->getCurrentPageResults(),
            'pager' => $pagerfanta,
        ]);
    }

    #[Route('/pokemon/show/{id}', name: 'pokemon_profile', methods: ['GET'])]
    public function show(string $id): Response
    {
        return $this->render('pokemon/show.html.twig', [
            'card' => $this->service->findById($id),
        ]);
    }
}
