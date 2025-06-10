<?php

namespace App\Controller;

use App\Form\SucursalType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sucursal;
use App\Services\MapService;
use Symfony\Component\HttpFoundation\Request;
final class SucursalController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private MapService $mapService) { }

    #[Route('/administracion/sucursal/nueva', name: 'app_crear_sucursal')]
    public function crear(Request $request): Response
       {

        $nuevaSucursal = new Sucursal();
        $form = $this->createForm(SucursalType::class, $nuevaSucursal);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  

            $verificarExistencia = $this->manager->getRepository(Sucursal::class)->findOneBy(['direccion' => $nuevaSucursal->getDireccion(), 'ciudad' => $nuevaSucursal->getCiudad()]);

            if($verificarExistencia ) {
                $this->addFlash('error', 'Ya existe una sucursal con esa direccion.');
                return $this->redirectToRoute('app_crear_sucursal');       
            }

            

            $coords = $this->mapService->calcularCoordenadasGeneral($nuevaSucursal->getDireccion(), $nuevaSucursal->getCiudad());

            if (!is_array($coords) || !isset($coords['lat'], $coords['lon'])) {
            $this->addFlash('error', 'La direccion no corresponde a una direccion valida dentro de la Provincia de Buenos Aires');
            return $this->redirectToRoute('app_crear_sucursal');
            }

            $nuevaSucursal->setLatitud($coords['lat']);
            $nuevaSucursal->setLongitud($coords['lon']);



            $this->manager->persist($nuevaSucursal);
            $this->manager->flush();

            $this->addFlash('success', 'Sucursal creada exitosamente.');
            return $this->redirectToRoute('app_crear_sucursal');
        }

        return $this->render('sucursal/nueva.html.twig', [
            'form' => $form,
        ]);
    }

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
