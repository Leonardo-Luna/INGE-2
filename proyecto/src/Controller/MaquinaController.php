<?php

namespace App\Controller;

use App\Entity\Cupon;
use App\Entity\EstadoReserva;
use App\Entity\Reserva;
use App\Entity\Maquina;
use App\Form\MaquinaType;
use App\Form\EditarMaquinaType;
use App\Services\MailService;
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

    public function __construct(private EntityManagerInterface $entityManager,
                                private MailService $mailService,) { }

    #[Route('/gerencia/maquina/nueva', name: 'app_maquina_nueva')]
    public function nueva(Request $request, SluggerInterface $slugger): Response
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
                            $currentImageFilenames[] = "maquinaria/" . $newFilename; // Añade el nuevo nombre a la lista
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Actualiza la entidad Maquina con los nuevos nombres de archivo
            $maquina->setImagenes($currentImageFilenames);

            $aux = $maquina->getReembolsoNormal();
            $maquina->setReembolsoNormal(($aux/100)*$maquina->getCostoPorDia());

            $aux = $maquina->getReembolsoPenalizado();
            $maquina->setReembolsoPenalizado(($aux/100)*$maquina->getCostoPorDia());

            $this->entityManager->persist($maquina);
            $this->entityManager->flush(); // Vuelve a guardar para actualizar los nombres de archivo

            $this->addFlash('success', 'Máquina creada y imágenes subidas correctamente.');
            return $this->redirectToRoute('app_maquina_nueva', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('maquina/nueva.html.twig', [
            'form' => $form, 
        ]);
    } 

    #[Route('/maquina/{id}', name: 'app_maquina_show')]
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

    #[Route('/maquina/{id}/fechas', name: 'app_maquina_fechas')]
    public function fechas(Maquina $maquina): Response
    {
        // La inyección de dependencias de Symfony automáticamente
        // buscará la Maquina con el ID proporcionado en la URL.
        // Si no la encuentra, lanzará un 404.
        $estado = $this->entityManager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $reservas = $this->entityManager->getRepository(Reserva::class)->filtrarPorMaquinaYEstado($maquina, $estado);

        return $this->render('maquina/fechas.html.twig', [
            'reservas' => $reservas,
            'maquina' => $maquina
        
        ]);
    }

    #[Route('/gerencia/maquina/eliminar/{id}', name: 'app_eliminar_maquina', methods: ['POST'])] # Es post, no se accede a la ruta sino que se le pega como una api pasando la id de la máquina como parámetro
    public function eliminarMaquina(Maquina $maquina)
    {
        $estadoAprobado = $this->entityManager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $estadoEnCurso = $this->entityManager->getRepository(EstadoReserva::class)->find(EstadoReserva::EN_CURSO)->getEstado();
        $tienePagos = $this->entityManager->getRepository(Maquina::class)->tieneAlquileres($maquina, $estadoAprobado);
        $tieneEnCurso = $this->entityManager->getRepository(Maquina::class)->tieneAlquileres($maquina, $estadoEnCurso);
        $estadoCancelada = $this->entityManager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);

        if($tieneEnCurso) { # Tirar error, la máquina no está en la sucursal, no se puede eliminar

            $this->addFlash('error', 'No se puede eliminar esta máquina, todavía tiene un alquiler en curso.');
            return $this->redirectToRoute('app_listar_maquinaria');
        }

        if($tienePagos) { # Hacer los reembolsos y mostrar mensaje de éxito 
            
            foreach($tienePagos->getReservas() as $pago) {
                # Enviar correo sobre el reembolso

                $montoCupon = $pago->getCostoTotal() * 0.15; # Variables temporales, a ojetes 2 no le gusta esto
                $usuario = $pago->getUsuario();
                $monto = $pago->getCostoTotal();
                
                $cupon = new Cupon();
                $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $cupon->setCodigo($codigo);
                $cupon->setMonto($montoCupon);

                $pago->setEstado($estadoCancelada->getEstado());
                $this->entityManager->persist($cupon); # Se persiste cada cupón creado, por eso adentro del loop 🤓 

                $this->mailService->reembolsoCupon($monto, $usuario->getEmail(), $montoCupon, $cupon->getCodigo(), $pago->getFechaInicio()->format('Y-m-d H:i:s'), $pago->getMaquina()->getNombre());
            }
        }

        # Si no se cumple ningún caso, se elimina sin problema
        $this->entityManager->remove($maquina);
        $this->entityManager->flush();
        $this->addFlash('success', 'Máquina eliminada existosamente.');
        return $this->redirectToRoute('app_listar_maquinaria');
    }

#[Route('/gerencia/maquina/editar/{id}', name: 'app_maquina_editar')]
public function editar(Request $request, Maquina $maquina, SluggerInterface $slugger): Response
{
    $form = $this->createForm(EditarMaquinaType::class, $maquina);
    $form->handleRequest($request);

    $imagenesActuales = $maquina->getImagenes() ?? [];

    if ($form->isSubmitted() && $form->isValid()) {

        $imagenesAEliminar = $request->request->all('eliminar_imagenes');
        if ($imagenesAEliminar) {
            foreach ($imagenesAEliminar as $rutaRelativa) {
                $key = array_search($rutaRelativa, $imagenesActuales);
                if ($key !== false) {
                    unset($imagenesActuales[$key]);
                }
            }
        }
        $imagenesSubidas = $form->get('imagenes')->getData();
        $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/images/maquinaria';

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        foreach ($imagenesSubidas as $imagenFile) {
            if ($imagenFile) {
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imagenFile->guessExtension();

                try {
                    $imagenFile->move($targetDirectory, $newFilename);
                    $imagenesActuales[] = 'maquinaria/' . $newFilename;
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                }
            }
        }

        $imagenesFinales = array_values($imagenesActuales); // Reindexar
        if (count($imagenesFinales) === 0) {
            $this->addFlash('error', 'Debes subir al menos una imagen.');
            return $this->render('maquina/editar.html.twig', [
                'form' => $form->createView(),
                'maquina' => $maquina,
            ]);
        }

        $maquina->setImagenes($imagenesFinales);

        $this->entityManager->flush();

        $this->addFlash('success', 'Máquina editada correctamente.');
        return $this->redirectToRoute('app_maquina_editar', ['id' => $maquina->getId()]);
    }

    return $this->render('maquina/editar.html.twig', [
        'form' => $form->createView(),
        'maquina' => $maquina,
    ]);
}

    #[Route('/administracion/listar-maquinaria', name: 'app_listar_maquinaria')]
    public function listarMaquinaria(): Response
    {
        $maquinas = $this->entityManager->getRepository(Maquina::class)->findAll();

        return $this->render('maquina/listar.html.twig', [
            'maquinas' => $maquinas,
        ]);
    }

        #[Route('/administacion/maquina/reparacion/{id}', name: 'app_maquina_reparacion')]
    public function reparacion(Maquina $maquina): Response
    {
        $maquina->setEnReparacion(!$maquina->isEnReparacion());
        $this->entityManager->flush();
        $maquinas = $this->entityManager->getRepository(Maquina::class)->findAll();
        
        return $this->render('maquina/listar.html.twig', [
            'maquinas' => $maquinas
        
        ]);
    }

    #[Route('/administracion/liberarMaquinaria/{id}', name: 'app_liberar_maquinaria', methods: ['GET', 'POST'])]
    public function liberarMaquinaria(Request $request, int $id): Response
    {   
        $maquina = $this->manager->getRepository(Maquina::class)->findOneBy(['id' => $id]);
        $maquina->setEnReparacion(false);

        $this->manager->flush();
        $this->addFlash('success', 'Maquina disponible correctamente.');
        return $this->render('admin/maquinaria.html.twig');
    }

   
}