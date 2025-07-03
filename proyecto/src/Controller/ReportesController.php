<?php

namespace App\Controller;

use App\Entity\EstadoReserva;
use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\Sucursal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReportesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager) {}

    #[Route('/gerencia/reportes/mas-reservas', name: 'app_reportes_mas_reservas')]
    public function masReservas(): Response
    {
        $estadoAprobada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $estadoFinalizado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FINALIZADO)->getEstado();
        $query = $this->manager->getRepository(User::class)->getClientesMasReservas($estadoAprobada, $estadoFinalizado);

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

    #[Route('/gerencia/reportes/mas-concurridas', name: 'app_reportes_mas_concurridas')]
    public function masConcurridas(): Response
    {
        $estadoAprobada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $estadoFinalizado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FINALIZADO)->getEstado();
        $query = $this->manager->getRepository(Sucursal::class)->getSucursalesMasConcurridas($estadoAprobada, $estadoFinalizado);

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

    #[Route('/gerencia/reportes/mas-alquiladas', name: 'app_reportes_mas_alquiladas')]
    public function masAlquiladas(): Response
    {
        $estadoAprobada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $estadoFinalizado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FINALIZADO)->getEstado();
        $query = $this->manager->getRepository(Maquina::class)->getMasAlquiladas($estadoAprobada, $estadoFinalizado);

        $labels = [];
        $data = [];
        foreach ($query as $row) {
            $labels[] = $row[0]->getNombre();
            $data[] = $row['reservasCount'];
        }

        return $this->render('reportes/mas-alquiladas.html.twig', [
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    #[Route('/gerencia/reportes/finanzas', name: 'app_reportes_finanzas')]
    public function finanzas(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('fecha_inicio', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de inicio',
                'required' => true,
            ])
            ->add('fecha_fin', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de fin',
                'required' => true,
            ])
            ->getForm();

        $query = "Seleccione un rango de fechas para mostrar los ingresos que hubo.";
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fechaInicio = $form->get('fecha_inicio')->getData();
            $fechaFin = $form->get('fecha_fin')->getData();
            $query = $this->manager->getRepository(Reserva::class)->buscarAlquileresEntre($fechaInicio, $fechaFin);
        }

        return $this->render('reportes/finanzas.html.twig', [
            'form' => $form->createView(),
            'monto' => $query,
        ]);
    }
}
