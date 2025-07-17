<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reserva;

final class AdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,) { }

    #[Route('/gerencia', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/administracion/maquinaria', name: 'panel_maquinaria')]
    public function maquinaria(): Response
    {
        return $this->render('admin/maquinaria.html.twig');
    }

    #[Route('/administracion/sucursales', name: 'panel_sucursales')]
    public function sucursales(): Response
    {
        return $this->render('admin/sucursales.html.twig');
    }

    #[Route('/administracion/usuarios', name: 'panel_usuarios')]
    public function usuarios(): Response
    {
        return $this->render('admin/usuarios.html.twig');
    }

    #[Route('/administracion/reservasyalquileres', name: 'panel_reservasyalquileres')]
    public function listarReservas(Request $request): Response
    {
        $reservas = $this->manager->getRepository(Reserva::class)->findAll();

        return $this->render('admin/reservasyalquileres.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/gerencia/reportes', name: 'app_listado_reportes')]
    public function listadoReportes(): Response
    {
        return $this->render('admin/listado-reportes.html.twig');
    }
}
