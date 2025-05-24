<?php

namespace App\Controller;

use App\Entity\Maquina;
use App\Form\MaquinaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface; 
use Symfony\Component\HttpFoundation\File\Exception\FileException; 
use Symfony\Component\HttpFoundation\File\UploadedFile; 

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
    public function nueva(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $maquina = new Maquina();
        $form = $this->createForm(MaquinaType::class, $maquina);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $imageFile = $form->get('rutaImagen')->getData(); // <<<<<< Obtener el archivo del formulario

            // Este if es para que solo se procese la imagen si se ha subido una
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Esto es necesario para incluir de forma segura el nombre del archivo como parte de la URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Mueve el archivo al directorio donde se guardarán las imágenes
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // <<<< Directorio configurado (ver c.3)
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... manejar excepción si algo sale mal durante la subida del archivo
                    $this->addFlash('error', 'Hubo un error al subir la imagen: ' . $e->getMessage());
                    return $this->render('maquina/nueva.html.twig', ['form' => $form]);
                }

                // Actualiza la propiedad 'rutaImagen' de la entidad para guardar el nombre del archivo
                $maquina->setRutaImagen($newFilename);
            }

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