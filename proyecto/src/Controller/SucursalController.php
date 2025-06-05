<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sucursal;

final class SucursalController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager) { }

    #[Route('/sucursal/{id}', name: 'app_visualizar_sucursal')]
    public function visualizar(int $id): Response
    {
        $sucursal = $this->manager->getRepository(Sucursal::class)->find($id);
        return $this->render('sucursal/visualizar.html.twig', [
            'sucursal' => $sucursal,
        ]);
    }

     #[Route('/administracion/sucursal/{id}', name: 'app_administrar_sucursal')]
    public function administrar(int $id): Response
    {
        $sucursal = $this->manager->getRepository(Sucursal::class)->find($id);
        return $this->render('sucursal/administrar.html.twig', [
            'sucursal' => $sucursal,
        ]);
    }


}
