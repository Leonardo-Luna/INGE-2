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
{
    #[Route('/administracion/maquina/nueva', name: 'app_maquina_nueva')]
    public function nueva(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $maquina = new Maquina();
        $form = $this->createForm(MaquinaType::class, $maquina);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[] $imagenesSubidas */
            $imagenesSubidas = $form->get('imagenes')->getData(); // Obtiene la colección de archivos subidos

            // Aquí debes decidir cómo vas a guardar las rutas de las imágenes asociadas a esta máquina.
            // Opción 1 (Recomendada para este enfoque): Un campo JSON en la entidad Maquina

            $currentImageFilenames = $maquina->getImagenes() ?? []; // Carga las existentes si las hay

            if ($imagenesSubidas) {
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/images/maquinaria';

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory, 0777, true);
                }

                foreach ($imagenesSubidas as $imagenFile) {
                    if ($imagenFile) {
                        $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imagenFile->guessExtension();

                        try {
                            $imagenFile->move(
                                $targetDirectory,
                                $newFilename
                            );
                            $currentImageFilenames[] = $newFilename; // Añade el nuevo nombre a la lista
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Actualiza la entidad Maquina con los nuevos nombres de archivo
            $maquina->setImagenes($currentImageFilenames);
            $entityManager->persist($maquina);
            $entityManager->flush(); // Vuelve a guardar para actualizar los nombres de archivo

            $this->addFlash('success', 'Máquina creada y imágenes subidas correctamente.');
            return $this->redirectToRoute('app_maquina_nueva', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('maquina/nueva.html.twig', [
            'form' => $form, 
        ]);
    }

    #[Route('/administracion/maquina/{id}', name: 'app_maquina_show')]
    public function show(Maquina $maquina): Response
    {
        // La inyección de dependencias de Symfony automáticamente
        // buscará la Maquina con el ID proporcionado en la URL.
        // Si no la encuentra, lanzará un 404.

        return $this->render('maquina/show.html.twig', [
            'maquina' => $maquina,
            'maquinaId' => $maquina->getId(),
        ]);
    }
}