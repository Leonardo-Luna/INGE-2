<?php

namespace App\Controller;

use App\Entity\Maquina;
use App\Form\MaquinaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MaquinaController extends AbstractController
#{
#    #[Route('/maquina', name: 'app_maquina')]
#    public function index(): Response
#    {
#        return $this->render('maquina/nueva.html.twig', [
#            'controller_name' => 'MaquinaController',
#        ]);
#    }
#}

{
    #[Route('/maquina/nueva', name: 'app_maquina_nueva')]
    public function nueva(Request $request, EntityManagerInterface $entityManager): Response
    {
        $maquina = new Maquina();
        $form = $this->createForm(MaquinaType::class, $maquina);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($maquina);
            $entityManager->flush();

            $this->addFlash('success', '¡Máquina guardada con éxito!');

            return $this->redirectToRoute('app_maquina_nueva');
        }

        return $this->render('maquina/nueva.html.twig', [
            'form' => $form, 
        ]);
    }
}