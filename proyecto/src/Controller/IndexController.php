<?php

namespace App\Controller;

use App\Services\MapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    public function __construct(private MapService $mapService) { }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        // Acá iría las cosas de sucursales, que es nuestro index
        return $this->render('index/index.html.twig', [
        ]);
    }

    #[Route('/test/mapa', name: 'app_mapa')]
    public function testMapa(Request $request): Response
    {
        $direccion = "Calle 64 525";

        $coordenadas = $this->mapService->calcularCoordenadas($direccion);

        return $this->render('index/mapa.html.twig', [
            'lat' => $coordenadas['lat'] ?? '',
            'lon' => $coordenadas['lon'] ?? '',
        ]);
    }
}