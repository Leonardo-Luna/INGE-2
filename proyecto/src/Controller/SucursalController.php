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

     #[Route('/administracion/sucursal/editar/{id}', name: 'app_editar_sucursal')]
    public function editarSucursal(int $id, Request $request, Sucursal $sucursal, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(SucursalType::class, $sucursal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $verificarExistencia = $this->manager->getRepository(Sucursal::class)->findOneBy(['direccion' => $sucursal->getDireccion(), 'ciudad' => $sucursal->getCiudad()]);

            if($verificarExistencia ) {
                if(!($id == $verificarExistencia->getId())) {
                    $this->addFlash('warning', 'Ya existe una sucursal con esa direccion.');
                    return $this->redirectToRoute('app_editar_sucursal',['id' => $sucursal->getId()]);       
                }
            }

            $coords = $this->mapService->calcularCoordenadasGeneral($sucursal->getDireccion(), $sucursal->getCiudad());

            if (!is_array($coords) || !isset($coords['lat'], $coords['lon'])) {
                $this->addFlash('warning', 'La direccion no corresponde a una direccion valida dentro de la Provincia de Buenos Aires');
                return $this->redirectToRoute('app_editar_sucursal',['id' => $sucursal->getId()]);
            }

            $sucursal->setLatitud($coords['lat']);
            $sucursal->setLongitud($coords['lon']);

            $entityManager->flush(); 

            $this->addFlash('success', '¡Sucursal actualizada con éxito!');

            // Redirige a la página de visualización de la sucursal 
            return $this->redirectToRoute('app_administrar_sucursal', ['id' => $sucursal->getId()]);
        }

        return $this->render('sucursal/editar.html.twig', [
            'sucursal' => $sucursal, // Pasar la sucursal para que la plantilla Twig pueda acceder a sus datos (nombre, etc.)
            'form' => $form,
        ]);
    }


}
