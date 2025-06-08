<?php

namespace App\Controller;

use App\Services\MailService;
use App\Entity\EstadoReserva;
use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Preference;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ReservaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,private MailService $mailService) { }

    #[Route('/mis-reservas', name: 'app_mis_reservas')]
    public function misReservas(Request $request): Response
    {
        $user = $this->getUser();

        $reservas = $this->manager->getRepository(Reserva::class)->filtrarPropias($user); # Método custom para mostrar propias :D

        return $this->render('reserva/listar-propias.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/reservas/eliminar', name: 'app_eliminar_reserva', methods: ['POST'])]
    public function eliminarReserva(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $id = $request->request->get('idR');
    $reserva = $entityManager->getRepository(Reserva::class)->findOneById($id);

    $hoy = new \DateTime();
    if  ($hoy < $reserva->getFechaReembolsoPenalizado()){
        $monto = $reserva->getMontoReembolso();
    }
    else{
        $monto = $reserva->getReembolsoPenalizado();
    }
    $usuario = $this->getUser();
    $this->mailService->reembolso($monto, $usuario->getEmail());

    $entityManager->remove($reserva);
    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}


    #[Route('/administracion/reservas', name: 'app_reservas')]
    public function listarReservas(Request $request): Response
    {
        $reservas = $this->manager->getRepository(Reserva::class)->findAll();

        return $this->render('reserva/listar-todas.html.twig', [
            "reservas" => $reservas,
        ]);
    }

    #[Route('/reserva/{id}', name: 'app_reserva_nueva')]
    public function index(int $id, Request $request): Response
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
        $reserva->setMontoReembolso($maquina->getReembolsoNormal() * $intervalo->days);

        $fechaPenalizacion = (clone $fechaInicio)->modify('-' . $maquina->getDiasReembolso() . ' days');
        $reserva->setFechaReembolsoPenalizado($fechaPenalizacion);
        $reserva->setReembolsoPenalizado($maquina->getReembolsoPenalizado());
        $costoOriginal=$maquina->getCostoPorDia() * $intervalo->days;

        $usuario = $this->getUser();
        if (!$usuario instanceof User) {
            throw $this->createAccessDeniedException('No hay usuario autenticado.');
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

        $this->manager->persist($reserva);
        $this->manager->flush();

        return $this->redirectToRoute('app_reservas_confirmar', ['id' => $reserva->getId()]);
        
    }

 

    #[Route('/reservas/{id}/confirmar', name: 'app_reservas_confirmar', methods: ['GET', 'POST'])]
    public function confirmarReserva(
        Reserva $reserva,
        EntityManagerInterface $entityManager,
        Request $request): Response {
        // Verificar que la reserva pertenezca al usuario actual o tenga un estado 'pendiente' adecuado
        if ($reserva->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Esta reserva no te pertenece.');
        }
        
        // Si la reserva ya está confirmada o pagada, redirigir
        if ($reserva->getEstado() !== 'FALTA DE PAGO') {
            $this->addFlash('error', 'La reserva ya ha sido procesada.');
            //return $this->redirectToRoute('app_mis_reservas');
        }

        //ya lo calcule, podria no hacerlo se supone que es por consistencia de que no hayan cambiando las cosas
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

        $costoOriginal = $reserva->getCostoTotal();
        $recargoMonto = $costoOriginal * $recargoPorcentaje;
        $costoFinalConRecargo = $costoOriginal + $recargoMonto;

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
            //"back_urls" => [
            //  "success" => $this->generateUrl('app_reserva_exito', ['id' => $reserva->getId()], true),
            //  "failure" => $this->generateUrl('app_reserva_error', ['id' => $reserva->getId()], true),
            //  "pending" => $this->generateUrl('app_reserva_pendiente', ['id' => $reserva->getId()], true),
            // ],
            //"auto_return" => "approved",
            //"notification_url" => $this->generateUrl('app_webhook', [], true),
        ]);

        // --- Lógica de confirmación (si el botón de pago fue presionado) ---
        if ($request->isMethod('POST')) {
            
        $pagoExitoso = true; // <-- ¡REEMPLAZAR CON LA LOGICA DE MERCADO PAGO!
        if ($pagoExitoso) {
            // Actualizar el estado de la reserva a CONFIRMADA
            $estadoConfirmada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
            
            $reserva->setEstado($estadoConfirmada);
            // Opcional: registrar la fecha de confirmación
            // $reserva->setFechaConfirmacion(new \DateTimeImmutable());

            $this->manager->flush(); // Solo necesitamos hacer flush, ya que la reserva ya está siendo manejada

            $this->addFlash('success', 'Tu reserva ha sido confirmada y pagada con éxito. Costo final: $' . number_format($reserva->getCostoTotal(), 2, ',', '.'));
            return $this->redirectToRoute('app_mis_reservas');
        } else {
            $this->addFlash('error', 'Hubo un problema al procesar tu pago. Por favor, inténtalo de nuevo.');
            // Redirigir de nuevo a la página de confirmación actual
            return $this->redirectToRoute('app_reservas_confirmar', ['id' => $reserva->getId()]);
        }
    }

        return $this->render('reserva/confirmar.html.twig', [
            'maquina' => $reserva->getMaquina(),
            'reserva' => $reserva, // La entidad Reserva se pasa completa
            'preferenceId' => $preference->id,
            'costoFinalDisplay' => $costoFinalConRecargo, // Este es el valor que mostrarás como "Costo total"
            'recargoMontoDisplay' => $recargoMonto, // Para mostrar el detalle del recargo
            'valoracionPromedio' => $valoracionPromedio, // Para depuración o información adicional
        ]);
    }


}
