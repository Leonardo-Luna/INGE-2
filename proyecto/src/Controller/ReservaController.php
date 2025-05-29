<?php

namespace App\Controller;

use App\Entity\Reserva;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager) { }

    #[Route('/reserva/todas', name: 'app_reserva_todas')]
    public function index(): Response
    {
        $reservas = $this->manager->getRepository(Reserva::class)->findAll();

        return $this->render('reserva/listado-todas.html.twig', [
            'reservas' => $reservas,
        ]);
    }
}
