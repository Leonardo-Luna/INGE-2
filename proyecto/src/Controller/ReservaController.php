<?php

namespace App\Controller;

use App\Services\MailService;
use App\Entity\EstadoReserva;
use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\User;
use App\Entity\Rol;

use App\Entity\Cupon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//por lo que vi vas a necesitar esto valen
use MercadoPago\Client\Point\PointClient; // Para el QR de punto de venta (empleado)
// use MercadoPago\Resources\Point\PaymentIntent;
use App\Services\QRService;

final class ReservaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private MailService $mailService, private QRService $qrService) { }

    #[Route('/mis-reservas', name: 'app_mis_reservas')]
    public function misReservas(Request $request): Response
    {
        $user = $this->getUser();

        $reservas = $this->manager->getRepository(Reserva::class)->filtrarReservasPropias($user); # Método custom para mostrar propias :D

        return $this->render('reserva/listar-propias.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/mis-alquileres', name: 'app_mis_alquileres')]
    public function misAlquileres(Request $request): Response
    {
        $user = $this->getUser();

        $alquileres = $this->manager->getRepository(Reserva::class)->filtrarAlquileresPropios($user);

        return $this->render('reserva/listar-alquileres-propios.html.twig', [
            "alquileres" => $alquileres,
        ]);
    }

    #[Route('/administracion/alquileres', name: 'app_todos_alquileres')]
    public function alquileresHistorico(Request $request): Response
    {
        if ($request->query->get('flash') === 'success') {
            $this->addFlash('success', 'Pago aprobado. La reserva ha sido confirmada.');
        }

        $estadoAprobado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
        $estadoEnCurso = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::EN_CURSO)->getEstado();
        $estadoFinalizado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FINALIZADO)->getEstado();

        $alquileres = $this->manager->getRepository(Reserva::class)->findBy([
            'estado' => [$estadoAprobado, $estadoEnCurso, $estadoFinalizado]
        ]);

        return $this->render('reserva/listar-alquileres-todos.html.twig', [
            "alquileres" => $alquileres,
        ]);
    }

     #[Route('/reservas/eliminarIndisponibilidad/{id}', name: 'app_eliminar_indisponibilidad', methods: ['GET','POST'])]
    public function eliminarReservaCuatro(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $reserva = $entityManager->getRepository(Reserva::class)->findOneById($id);
        $estadoCancelada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);

        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $montoCupon = $reserva->getCostoTotal() * 0.15;
        
        $cupon = new Cupon();
        $cupon->setCodigo($codigo);
        $cupon->setMonto($montoCupon);

        $entityManager->persist($cupon);

        $monto = $reserva->getCostoTotal();

        $usuario = $reserva->getUsuario();

        $this->mailService->reembolsoCupon($monto, $usuario->getEmail(), $montoCupon, $cupon->getCodigo(), $reserva->getFechaInicio()->format('Y-m-d H:i:s'), $reserva->getMaquina()->getNombre());
        
        $reserva->setEstado($estadoCancelada->getEstado());
        $entityManager->flush();

        $this->addFlash('error', 'Se canceló la reserva exitosamente.');
        return $this->redirectToRoute('app_reservas');
    }

    #[Route('/reservas/eliminar', name: 'app_eliminar_reserva', methods: ['GET','POST'])]
    public function eliminarReserva(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->request->get('idR');
        $reserva = $entityManager->getRepository(Reserva::class)->findOneById($id);
        $estadoCancelada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);


        $hoy = new \DateTime();
        if  ($hoy < $reserva->getFechaReembolsoPenalizado()){
            $monto = $reserva->getMontoReembolso();
        }
        else{
            $monto = $reserva->getReembolsoPenalizado();
        }
        $usuario = $this->getUser();
        if($monto > 0){
        $this->mailService->reembolso($monto, $usuario->getEmail());
        }

        $reserva->setEstado($estadoCancelada->getEstado());
        $entityManager->flush();
        $this->redirectToRoute('app_mis_alquileres');
        return new JsonResponse(['success' => true]);
    }

    #[Route('/reservas/eliminar/{id}', name: 'app_eliminar_reserva_id', methods: ['GET','POST'])]
    public function eliminarReservaID(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reserva = $entityManager->getRepository(Reserva::class)->findOneById($id);
        $estadoCancelada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);


        $hoy = new \DateTime();
        if  ($hoy < $reserva->getFechaReembolsoPenalizado()){
            $monto = $reserva->getMontoReembolso();
        }
        else{
            $monto = $reserva->getReembolsoPenalizado();
        }
        $usuario = $this->getUser();
        if($monto > 0){
        $this->mailService->reembolso($monto, $usuario->getEmail());
        }

        $reserva->setEstado($estadoCancelada->getEstado());
        $entityManager->flush();

        $this->addFlash('error', 'Se canceló la reserva exitosamente.');
        return $this->redirectToRoute('app_reservas');
    }


    #[Route('/reservas/eliminarEmpleado/{id}', name: 'app_eliminar_reserva_id_empleado', methods: ['GET','POST'])]
    public function eliminarReservaIDEmpleado(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reserva = $entityManager->getRepository(Reserva::class)->findOneById($id);
        $estadoCancelada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);


        $hoy = new \DateTime();
        if  ($hoy < $reserva->getFechaReembolsoPenalizado()){
            $monto = $reserva->getMontoReembolso();
        }
        else{
            $monto = $reserva->getReembolsoPenalizado();
        }
        $usuario = $reserva->getUsuario();
        if($monto > 0){
        $this->mailService->reembolso($monto, $usuario->getEmail());
        }

        $reserva->setEstado($estadoCancelada->getEstado());
        $entityManager->flush();

        $this->addFlash('error', 'Se canceló la reserva exitosamente.');
        return $this->redirectToRoute('app_reservas');
    }

    #[Route('/reserva/eliminar/{id}', name: 'reserva_eliminar', methods: ['POST'])]
    public function eliminar(int $id): JsonResponse
    {
    $reserva = $this->manager->getRepository(Reserva::class)->findOneById($id);
$estadoAprobado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA);
    $estadoCancelada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);
    if ($reserva->getEstado()!=$estadoAprobado->getEstado()){
    $reserva->setEstado($estadoCancelada->getEstado());
    }
    
    $this->manager->flush();

    return new JsonResponse(['success' => true]);
    }


    #[Route('/administracion/reservas', name: 'app_reservas')]
    public function listarReservas(Request $request): Response
    {
        $reservas = $this->manager->getRepository(Reserva::class)->buscarReservas();

        return $this->render('reserva/listar-todas.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/reserva/{id}', name: 'app_reserva_nueva', methods: ['GET', 'POST'])]
    public function index(int $id, Request $request, UserRepository $userRepository): Response
    {
        $maquina = $this->manager->getRepository(Maquina::class)->find($id);
        
        $reserva = new Reserva();

        $fechaInicio = new \DateTime($request->request->get('fecha_inicio'));
        $fechaFin = new \DateTime($request->request->get('fecha_fin'));

        $reserva->setFechaInicio($fechaInicio);
        $reserva->setFechaFin($fechaFin);

        $estadoFaltaPago = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FALTA_PAGO)->getEstado();
        
        $reserva->setEstado($estadoFaltaPago);
        $reserva->setMaquina($maquina);

        $intervalo = $fechaInicio->diff($fechaFin);
        $reserva->setMontoReembolso($maquina->getReembolsoNormal() * ($intervalo->days+1));

        $fechaPenalizacion = (clone $fechaInicio)->modify('-' . $maquina->getDiasReembolso() . ' days');
        $reserva->setFechaReembolsoPenalizado($fechaPenalizacion);
        $reserva->setReembolsoPenalizado($maquina->getReembolsoPenalizado() * ($intervalo->days+1));
        $costoOriginal=$maquina->getCostoPorDia() * ($intervalo->days+1);

        if ($this->isGranted('ROLE_CLIENTE')) {
            $usuario = $this->getUser();
        } else { 
            $dniClienteEmpleado = $request->request->get('dni_cliente_empleado');
            $rolCliente = $this->manager->getRepository(Rol::class)->find(Rol::CLIENTE)->getNombre();
            $clientePorDni = $userRepository->findOneByDniAndRole($dniClienteEmpleado, $rolCliente); 

            if ($clientePorDni) {
                $usuario = $clientePorDni;
                
            } else {
                $this->addFlash('error', 'El DNI del cliente proporcionado no está registrado.');
                return $this->redirectToRoute('app_maquina_fechas', ['id' => $maquina->getId()]);
            }
        }

        $reserva->setUsuario($usuario);

        $valoracionTotal = $usuario->getValoracionTotal();
        $cantValoraciones = $usuario->getCantValoraciones();

        $recargoPorcentaje = 0;
        $valoracionPromedio = 0;

        if ($cantValoraciones > 0) {
            $valoracionPromedio = $valoracionTotal / $cantValoraciones;
            if ($valoracionPromedio <= 1) {
                $recargoPorcentaje = 0.30; // 30% para 1 estrella o menos
            } elseif ($valoracionPromedio <= 2) {
                $recargoPorcentaje = 0.20; // 20% para 2 estrellas o menos
            } elseif ($valoracionPromedio <= 3) {
                $recargoPorcentaje = 0.10; // 10% para 3 estrellas o menos
            }
        }
        
        $recargoMonto = $costoOriginal * $recargoPorcentaje;
        $costoFinalConRecargo = $costoOriginal + $recargoMonto;

        $reserva->setCostoTotal($costoFinalConRecargo);
        $reserva->setCreacion(new DateTime());

        $this->manager->persist($reserva);
        $this->manager->flush();

        return $this->redirectToRoute('app_reservas_confirmar', ['id' => $reserva->getId()]);
    }
    
 

    #[Route('/reservas/{id}/confirmar', name: 'app_reservas_confirmar', methods: ['GET', 'POST'])]
    public function confirmarReserva(
        Reserva $reserva,
        Request $request,
        UserRepository $userRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        // Si la reserva ya está confirmada o pagada, redirigir
        if ($reserva->getEstado() !== 'FALTA DE PAGO') {
            $this->addFlash('error', 'La reserva ya ha sido procesada.');
            return $this->redirectToRoute('app_mis_reservas');
        }

        // Cálculo del costo (mantiene la lógica existente)
        $usuarioReserva = $reserva->getUsuario();
        $valoracionTotal = $usuarioReserva->getValoracionTotal();
        $cantValoraciones = $usuarioReserva->getCantValoraciones();

        $recargoPorcentaje = 0;
        $valoracionPromedio = 0;

        if ($cantValoraciones > 0) {
            $valoracionPromedio = $valoracionTotal / $cantValoraciones;
            if ($valoracionPromedio <= 1) {
                $recargoPorcentaje = 0.30;
            } elseif ($valoracionPromedio <= 2) {
                $recargoPorcentaje = 0.20;
            } elseif ($valoracionPromedio <= 3) {
                $recargoPorcentaje = 0.10;
            }
        }
        $intervalo = $reserva->getFechaInicio()->diff($reserva->getFechaFin());
        $costoOriginal = $reserva->getMaquina()->getCostoPorDia() * ($intervalo->days + 1);
        $costoFinalConRecargo = $reserva->getCostoTotal();
        $recargoMonto = $reserva->getMaquina()->getCostoPorDia() * $recargoPorcentaje * ($intervalo->days + 1);
        $url = $this->generateUrl('app_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $idReserva = $reserva->getId();

        if ($this->isGranted('ROLE_CLIENTE')) {
            // --- Lógica de Mercado Pago para pago ONLINE ---
            MercadoPagoConfig::setAccessToken($_ENV['MERCADOPAGO_ACCESS_TOKEN']); 
            $client = new PreferenceClient(); 
            $preference = $client->create([
                "items" => [
                    [
                        "title" => "Reserva de Maquina",
                        "quantity" => 1,
                        "unit_price" => $costoFinalConRecargo,
                    ],
                ],
                "statement_descriptor" => "Alquil.AR",
                "external_reference" => $reserva->getId(),
                "back_urls" => [
                    "success" => $this->generateUrl('app_reserva_exito', ['id' => $reserva->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "failure" => $this->generateUrl('app_reserva_error', ['id' => $reserva->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
                "auto_return" => "approved", 
                "notification_url" => $this->generateUrl('app_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
            $qrData = null; 
            return $this->render('reserva/confirmar.html.twig', [
                'maquina' => $reserva->getMaquina(),
                'reserva' => $reserva,
                'preferenceId' => $preference->id,
                'costoFinalDisplay' => $costoFinalConRecargo,
                'recargoMontoDisplay' => $recargoMonto,
                'valoracionPromedio' => $valoracionPromedio,
                'qr_data' => $qrData, 
            ]);

        }else{
            $qrData = $this->qrService->generarOrdenQr($costoFinalConRecargo, $idReserva, $url);
            if (!$qrData) {
                $this->addFlash('error', 'No se pudo generar el QR de pago. Intente nuevamente.');
            }
            return $this->render('reserva/confirmar.html.twig', [
                'maquina' => $reserva->getMaquina(),
                'reserva' => $reserva,
                'costoFinalDisplay' => $costoFinalConRecargo,
                'recargoMontoDisplay' => $recargoMonto,
                'valoracionPromedio' => $valoracionPromedio,
                'qr_data' => $qrData, 
            ]);
        }
    }


    #[Route('/reservas/{id}/exito', name: 'app_reserva_exito')]
    public function reservaExito(int $id, Request $request): Response {
        $reserva = $this->manager->getRepository(Reserva::class)->find($id);

        if (!$reserva) {
            throw $this->createNotFoundException('Reserva no encontrada.');
        }

        $this->addFlash('success', 'Pago aprobado. Tu reserva ha sido confirmada.');


        return $this->redirectToRoute('app_mis_alquileres');
    }
    
    #[Route('/administracion/{id}/valorar', name: 'app_reserva_valorar')]
    public function valorarAlquiler(
        Reserva $alquiler, 
        Request $request
    ): Response {
        if ($request->isMethod('POST')) {
            $estrellas = $request->request->get('puntuacion'); // Obtén el valor del campo 'puntuacion' del formulario POST
            $comentario = $request->request->get('comentario'); // Obtén el valor del campo 'comentario'
            if ($comentario === null || $comentario === '') {
            $comentario = 'Todo en orden! Gracias';
            }

            // Valida que 'estrellas' sea un número válido entre 1 y 5
            if (!is_numeric($estrellas) || $estrellas < 1 || $estrellas > 5) {
                $this->addFlash('error', 'La puntuación debe ser un número entre 1 y 5.');
                // Renderiza la vista de nuevo para que el usuario corrija 
                return $this->render('reserva/valorar.html.twig', [
                    'alquiler' => $alquiler,
                    // Pasar los valores que ya había ingresado para pre-rellenar
                    'puntuacion_anterior' => $estrellas,
                    'comentario_anterior' => $comentario,
                ]);
            }

            // Obtener el usuario
            /** @var User $userToRate */
            $userToRate = $alquiler->getUsuario(); 

            if (!$userToRate) {
                $this->addFlash('error', 'No se pudo encontrar al usuario propietario de esta máquina para valorar.');
                return $this->redirectToRoute('app_catalogo');
            }

            // --- Actualizar los puntos y cantidad de valoraciones del usuario ---
            $userToRate->setValoracionTotal(
                $userToRate->getValoracionTotal() + (int)$estrellas
            );
            $userToRate->setCantValoraciones(
                $userToRate->getCantValoraciones() + 1
            );
            $this->manager->flush();

            $this->mailService->EnviarComentario($comentario,$userToRate->getEmail(), $alquiler->getMaquina()->getNombre(), (string)$estrellas);
            $this->addFlash('success', '¡Gracias por tu valoración!');
            return $this->redirectToRoute('app_reservas');
        }

        // --- 3. Mostrar el formulario de valoración (GET) ---
        return $this->render('reserva/valorar.html.twig', [
            'alquiler' => $alquiler,
            // pasa los valores anteriores si hubo un error en POST
            'puntuacion_anterior' => $request->request->get('puntuacion'),
            'comentario_anterior' => $request->request->get('comentario'),
        ]);
    }

    #[Route('/reservas/{id}/error', name: 'app_reserva_error')]
    public function reservaError(int $id): Response {
        $this->addFlash('error', 'El pago ha fallado. No se ha confirmado tu reserva.');
        return $this->redirectToRoute('app_mis_reservas');
    }

    #[Route('/reservas/{id}/pendiente', name: 'app_reserva_pendiente')]
    public function reservaPendiente(int $id): Response {
        $this->addFlash('warning', 'Tu pago está pendiente. Te notificaremos cuando se confirme.');
        return $this->redirectToRoute('app_mis_reservas');
    }

    #[Route('/webhook/mercadopago', name: 'app_webhook', methods: ['POST'])]
    public function webhookMercadoPago(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['type']) || $data['type'] !== 'payment') {
            return new Response('Tipo no manejado', Response::HTTP_BAD_REQUEST);
        }

        // Obtener el ID del pago
        $paymentId = $data['data']['id'];

        // Consultar el estado del pago a la API de MercadoPago
        MercadoPagoConfig::setAccessToken($_ENV['MERCADOPAGO_ACCESS_TOKEN']);

        $paymentClient = new \MercadoPago\Client\Payment\PaymentClient();
        $payment = $paymentClient->get($paymentId);

        $externalReference = $payment->external_reference;
        $status = $payment->status; // approved, pending, rejected, etc.

        // Buscar la reserva por external_reference (ID que enviaste en Preference)
        $reserva = $this->manager->getRepository(Reserva::class)->find($externalReference);
        if (!$reserva) {
            return new Response('Reserva no encontrada', Response::HTTP_NOT_FOUND);
        }

        // Cambiar estado según resultado
        if ($status === 'approved') {
            $estado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
            $this->mailService->EnviarReservaFinalizada($reserva->getCostoTotal(), $reserva->getUsuario()->getEmail(), $reserva->getMaquina()->getNombre(), $reserva->getFechaInicio());
        } else {
            $estado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FALTA_PAGO)->getEstado();
        }
        $reserva->setEstado($estado);
        $this->manager->flush();
        return new Response('Webhook procesado', Response::HTTP_OK);
}
 
#[Route('/aplicar-cupon', name: 'aplicar_cupon', methods: ['POST'])]
    public function aplicarCupon(Request $request): JsonResponse
    {   $data = json_decode($request->getContent(), true);
        $codigo = $data['codigo'] ?? null;
        $id = $data['id'] ?? null;
        $cupon = $this->manager->getRepository(Cupon::class)->findOneBy(['codigo' => $codigo]);  
        if (!$cupon) {
            return new JsonResponse(['mensaje' => 'Código inválido'], 203);
        }
       $reserva= $this->manager->getRepository(Reserva::class)->findOneById($id);
        // Aca se puede comprobar que haber tomado la decision de expresar el reembolso en cantidad por dia fue mala idea (mia btw).
        // Dado que si refactorizo ese codigo voy a romper absolutamente todo, vamos a seguir con la deuda tecnica con las siguientes cuentas matematicas :)

        $mulNormal = $reserva->getMontoReembolso() / $reserva->getCostoTotal();
        $mulPenalizado = $reserva->getReembolsoPenalizado() / $reserva->getCostoTotal();
        $reserva->setMontoReembolso( $reserva->getMontoReembolso() -  $cupon->getMonto() * $mulNormal);
        $reserva->setReembolsoPenalizado( $reserva->getReembolsoPenalizado() -  $cupon->getMonto() * $mulPenalizado);
        $reserva->setCostoTotal($reserva->getCostoTotal() - $cupon->getMonto());

        if ($reserva->getMontoReembolso()< 0){
            $reserva->setMontoReembolso(0);
        }
        if ($reserva->getReembolsoPenalizado()< 0){
            $reserva->setMontoReembolso(0);
        }
        if ($reserva->getCostoTotal()< 0){
            $reserva->setCostoTotal(0);
        }
        
        $this->manager->remove($cupon);
        $this->manager->flush();
        
        
        return new JsonResponse(['mensaje' => 'Cupón aplicado: ' . $codigo,
                                'reservaCosto' => $reserva->getCostoTotal(),
                                'descuento'=>$cupon->getMonto(),
                                'reservaR1'=>$reserva->getMontoReembolso(),
                                'reservaR2'=> $reserva->getReembolsoPenalizado()]);
    }

#[Route('/administracion/devolverMaquinaria/{id}', name: 'app_devolver_maquinaria', methods: ['GET', 'POST'])]
    public function devolverMaquinaria(Request $request, int $id): Response
    {   
        $reserva = $this->manager->getRepository(Reserva::class)->findOneBy(['id' => $id]);
        $maquina = $reserva->getMaquina();
        $estado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::FINALIZADO)->getEstado();
        $reserva->setEstado($estado);
        $maquina->setEnReparacion(1);

        $this->manager->flush();

        return $this->redirectToRoute('app_reserva_valorar', ['reserva' => $reserva, 'id' => $reserva->getId()]);

    }

    #[Route('/administracion/entregarMaquinaria/{id}', name: 'app_entregar_maquinaria', methods: ['GET', 'POST'])]
    public function entregarMaquinaria(Request $request, int $id): Response
    {   
        $reserva = $this->manager->getRepository(Reserva::class)->findOneBy(['id' => $id]);
        $estado = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::EN_CURSO)->getEstado();
        $reserva->setEstado($estado);
        $this->manager->persist($reserva);


        $this->manager->flush();
        $reservas = $this->manager->getRepository(Reserva::class)->findAll();
        $this->addFlash('success', 'Maquina entregada correctamente.');
        return $this->render('reserva/listar-todas.html.twig', [
            "reservas" => $reservas,
        ]);

    }
    
    #[Route('/reserva/{id}/estado', name: 'app_reserva_estado', methods: ['GET'])]
    public function estadoReserva(int $id): JsonResponse
    {
        $reserva = $this->manager->getRepository(Reserva::class)->find($id);
        if (!$reserva) {
            return new JsonResponse(['error' => 'Reserva no encontrada'], 404);
        }

        return new JsonResponse(['estado' => $reserva->getEstado()]);
    }
}
