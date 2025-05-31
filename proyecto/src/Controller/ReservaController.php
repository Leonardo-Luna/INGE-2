<?php

namespace App\Controller;

use App\Controller\MaquinaController;
use App\Entity\Maquina;
use App\Entity\Reserva;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;  

final class ReservaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager) { }

    #[Route('/reserva/{id}', name: 'app_reserva_todas')]
    public function index(int $id, Request $request): Response
    {
        $maquina = $this->manager->getRepository(Maquina::class)->find($id);
        $reserva = new Reserva();

        $reserva->setFechaInicio(new \DateTime('2025-06-01'));
        $reserva->setFechaFin(new \DateTime('2025-06-09'));
        
        $fechaInicio = $reserva->getFechaInicio();
        $fechaFin = $reserva->getFechaFin();

        $reserva->setEstado("Pendiente");

        $reserva->setMaquina($maquina);

        $intervalo = $fechaInicio->diff($fechaFin);
        $reserva->setMontoReembolso($maquina->getReembolsoNormal()*$intervalo->days);
        $fechaPenalizacion = (clone $fechaInicio)->modify('-' . $maquina->getDiasReembolso() . ' days');
        $reserva->setFechaReembolsoPenalizado($fechaPenalizacion);

        $reserva->setReembolsoPenalizado($maquina->getReembolsoPenalizado());
        
        $reserva->setCostoTotal($maquina->getCostoPorDia() * $intervalo->days);

        $this->manager->persist($reserva);
        $this->manager->flush();

        return $this->render('reserva/confirmar.html.twig', [
            'maquina' => $maquina,
            'reserva' => $reserva
        ]);
    }
}
