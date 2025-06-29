<?php

namespace App\Controller;

use App\Entity\EstadoReserva;
use App\Entity\Sucursal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReportesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager) {}

    #[Route('/reportes/mas-reservas', name: 'app_reportes_mas_reservas')]
    public function masReservas(): Response
    {
        $estadoAprobada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $query = $this->manager->getRepository(User::class)->getClientesMasReservas($estadoAprobada);

        $labels = [];
        $data = [];
        foreach ($query as $row) {
            $labels[] = $row[0]->getNombre() . ' ' . $row[0]->getApellido();
            $data[] = $row['reservasAprobadas'];
        }

        return $this->render('reportes/mas-reservas.html.twig', [
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    #[Route('/reportes/mas-concurridas', name: 'app_reportes_mas_concurridas')]
    public function masConcurridas(): Response
    {
        $estadoAprobada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $query = $this->manager->getRepository(Sucursal::class)->getSucursalesMasConcurridas($estadoAprobada);

        $labels = [];
        $data = [];
        foreach ($query as $row) {
            $labels[] = $row[0]->getNombre();
            $data[] = $row['reservasCount'];
        }

        return $this->render('reportes/mas-concurridas.html.twig', [
            'labels' => $labels,
            'data' => $data,
        ]);
    }
}
