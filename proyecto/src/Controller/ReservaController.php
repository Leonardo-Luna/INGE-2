<?php

namespace App\Controller;

use App\Entity\Reserva;
use App\Form\ReservaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reserva', name: 'reserva_')]
class ReservaController extends AbstractController
{
    #[Route('/nueva', name: 'nueva')]
    public function nueva(Request $request, EntityManagerInterface $em): Response
    {
        $reserva = new Reserva();
        $form = $this->createForm(ReservaType::class, $reserva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reserva);
            $em->flush();
            return $this->redirectToRoute('reserva_confirmacion'); // o a donde quieras
        }

        return $this->render('reserva/nueva.html.twig', [
            'formulario' => $form->createView(),
        ]);
    }
}
