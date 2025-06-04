<?php

namespace App\Controller;

use App\Entity\EstadoReserva;
use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class ReservaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager) { }

    #[Route('/mis-reservas', name: 'app_mis_reservas')]
    public function misReservas(Request $request): Response
    {
        $user = $this->getUser();

        $reservas = $this->manager->getRepository(Reserva::class)->filtrarPropias($user); # Método custom para mostrar propias :D

        return $this->render('reserva/listar-propias.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/administracion/reservas', name: 'app_reservas')]
    public function listarReservas(Request $request): Response
    {
        $reservas = $this->manager->getRepository(Reserva::class)->findAll();

        return $this->render('reserva/listar-todas.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/reserva/{id}', name: 'app_reserva_nueva')]
    public function index(int $id, Request $request): Response
    {
        $maquina = $this->manager->getRepository(Maquina::class)->find($id);
        $reserva = new Reserva();

        $fechaInicio = new \DateTime($request->request->get('fecha_inicio'));
        $fechaFin = new \DateTime($request->request->get('fecha_fin'));

        $reserva->setFechaInicio($fechaInicio);
        $reserva->setFechaFin($fechaFin);    

        $estadoFaltaPago = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FALTA_PAGO)->getEstado();
        
        $reserva->setEstado($estadoFaltaPago);
        $reserva->setMaquina($maquina);

        $intervalo = $fechaInicio->diff($fechaFin);
        $reserva->setMontoReembolso($maquina->getReembolsoNormal() * $intervalo->days);

        $fechaPenalizacion = (clone $fechaInicio)->modify('-' . $maquina->getDiasReembolso() . ' days');
        $reserva->setFechaReembolsoPenalizado($fechaPenalizacion);
        $reserva->setReembolsoPenalizado($maquina->getReembolsoPenalizado());
        $reserva->setCostoTotal($maquina->getCostoPorDia() * $intervalo->days);

        // ✅ Obtener el usuario autenticado correctamente
        $usuario = $this->getUser();
        if (!$usuario instanceof User) {
            throw $this->createAccessDeniedException('No hay usuario autenticado.');
        }

        $reserva->setUsuario($usuario);

        $this->manager->persist($reserva);
        $this->manager->flush();

        return $this->render('reserva/confirmar.html.twig', [
            'maquina' => $maquina,
            'reserva' => $reserva
        ]);
    }
}
