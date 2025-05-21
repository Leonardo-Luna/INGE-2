<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    public function __construct(private RequestStack $requestStack) { }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        // Acá iría las cosas de sucursales, que es nuestro index
        return $this->render('index/index.html.twig', [
        ]);
    }
}
