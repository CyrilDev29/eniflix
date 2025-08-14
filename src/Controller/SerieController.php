<?php

namespace App\Controller;

use App\Repository\SerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/serie', name: 'serie')]
final class SerieController extends AbstractController
{

    #[Route('/list/{page}', name: '_list', requirements: ['page' => '\d+'], defaults: ['page' => 1], methods: ['GET'])]
    public function list(SerieRepository $serieRepository, int $page, ParameterBagInterface $parameters): Response
    {
        // Récupération de la configuration
        $nbPerPage = $parameters->get('serie')['nb_max'];
        $offset = ($page - 1) * $nbPerPage;
        $criterias = [
            'status' => 'Returning',
            'genre' => 'Drama',
        ];

        // Récupération des séries avec pagination
        $series = $serieRepository->findBy(
            $criterias,
            ['popularity' => 'DESC'],
            $nbPerPage,
            $offset,
        );

        // Calcul du nombre total de pages
        $total = $serieRepository->count($criterias);
        $totalPages = ceil($total / $nbPerPage);

        return $this->render('serie/list.html.twig', [
            'series' => $series,
            'page' => $page,
            'total_pages' => $totalPages,
        ]);
    }

    #[Route('/liste-custom', name: '_custom_list')]
    public function listCustom(SerieRepository $serieRepository): Response
    {

        // $series = $serieRepository->findSeriesByCustom(400.0, 8.0);
        $series = $serieRepository->findSeriesWithDQL(400.0, 8.0);

        // le requêtage SQL raw nécessite qu'on adapte le templace (firstAirDate -> first_air_date )
        //$series = $serieRepository->findSeriesWithSQL(400.0, 8.0);


        return $this->render('serie/list.html.twig',[
            'series' => $series,
            'page' => 1,
            'total_pages' => 1,
        ]);
    }

    #[Route('/detail/{id}', name: '_detail')]
    public function detail(int $id, SerieRepository $serieRepository): Response
    {

        $serie = $serieRepository->find($id);


        // $serie = $serieRepository->findSerieByIdCustom($id);

        if (!$serie) {
            throw $this->createNotFoundException('Pas de série pour cet id');
        }

        return $this->render('serie/detail.html.twig', [
            'serie' => $serie,
        ]);
    }
}