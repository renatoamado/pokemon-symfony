<?php

namespace App\Controller;

use App\Service\PokemonService;
use Exception;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokemonController extends AbstractController
{
    function __construct(private readonly PokemonService $service)
    {
    }

    #[Route('/pokemon', name: 'pokemon_list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        try {
            $cards = $this->service->findAll();
        } catch (Exception $exception) {
            return $this->render('pokemon/index.html.twig', [
                'error' => $exception->getMessage(),
            ]);
        }

        $adapter = new ArrayAdapter($cards);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage((int)$request->query->get('page', 1));

        return $this->render('pokemon/index.html.twig', [
            'cards' => $pagerfanta->getCurrentPageResults(),
            'pager' => $pagerfanta,
            'error' => null,
        ]);
    }

    #[Route('/pokemon/show/{id}', name: 'pokemon_profile', methods: ['GET'])]
    public function show(string $id): Response
    {
        try {
            $card = $this->service->findById($id);
        } catch (Exception $exception) {
            return $this->render('pokemon/show.html.twig', [
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->render('pokemon/show.html.twig', [
            'card' => $card,
            'error' => null,
        ]);
    }
}
