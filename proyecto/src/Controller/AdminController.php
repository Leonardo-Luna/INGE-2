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
     public function __construct(private EntityManagerInterface $manager){ }

    #[Route('/administracion', name: 'app_admin')]
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

    #[Route('/administracion/reportes', name: 'panel_reportes')]
    public function reportes(): Response
    {
        return $this->render('admin/reportes.html.twig');
    }

    #[Route('/administracion/reservas', name: 'app_reservas')]
    public function listarReservas(Request $request): Response
    {
        $reservas = $this->manager->getRepository(Reserva::class)->findAll();

        return $this->render('reserva/listar-todas.html.twig', [
            "reservas" => $reservas,
        ]);
    }

}
