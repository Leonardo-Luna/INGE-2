<?php

namespace App\Controller;

use App\Entity\Maquina;
use App\Entity\Sucursal;
use App\Services\MapService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MaquinaRepository; 
use App\Repository\SucursalRepository; 

final class IndexController extends AbstractController
{
    public function __construct(private MapService $mapService,
                                private EntityManagerInterface $manager) { }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $sucursales = $this->manager->getRepository(Sucursal::class)->findAll();

        return $this->render('index/sucursales.html.twig', [
            "sucursales" => $sucursales,
        ]);
    }    

    #[Route('/catalogo/{idSucursal}', name: 'app_catalogo', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function catalogoPorSucursal(Request $request, ?int $idSucursal = null): Response {
        $search = $request->query->get('search');
        $disponibilidad = $request->query->get('disponibilidad');
        $tipo = $request->query->get('tipo');
        $sucursalId = $request->query->get('sucursal');
        
        if ($idSucursal) {
            $sucursalId = $idSucursal;
        }

        $maquinas = $this->manager->getRepository(Maquina::class)->findFiltered(
            $search,
            $disponibilidad,
            $tipo,
            $sucursalId ? (int)$sucursalId : null 
        );

        $tiposMaquina = $this->manager->createQueryBuilder()
            ->select('DISTINCT m.tipo')
            ->from(Maquina::class, 'm')
            ->where('m.tipo IS NOT NULL')
            ->orderBy('m.tipo', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $sucursalesList = $this->manager->getRepository(Sucursal::class)->findAll();

        return $this->render('index/catalogo.html.twig', [
            'maquinas' => $maquinas,
            'tipos_maquina' => $tiposMaquina,
            'sucursales_list' => $sucursalesList,
            'current_sucursal_id' => $sucursalId ? (int)$sucursalId : null,
        ]);
    }

    #[Route('/mi-perfil', name: 'app_perfil')]
    public function perfil(): Response
    {
        $user = $this->getUser();

        return $this->render('index/mi-perfil.html.twig', [
            'user' => $user,
        ]);
    }

    // #[Route('/test/mapa', name: 'app_mapa')]
    // public function testMapa(Request $request): Response
    // {
    //     $direccion = "Calle 64 525";

    //     $coordenadas = $this->mapService->calcularCoordenadas($direccion);

    //     return $this->render('index/mapa.html.twig', [
    //         'lat' => $coordenadas['lat'] ?? '',
    //         'lon' => $coordenadas['lon'] ?? '',
    //     ]);
    // }
}
