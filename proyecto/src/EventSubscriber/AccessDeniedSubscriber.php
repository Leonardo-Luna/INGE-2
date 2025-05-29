<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AccessDeniedSubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;

    public function __construct(UrlGeneratorInterface $urlGenerator, Security $security)
    {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security; // Inyecta el servicio Security
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Solo actuamos si la excepción es AccessDeniedHttpException
        if (!$exception instanceof AccessDeniedHttpException) {
            return;
        }

        // Si el usuario no está autenticado (o es un usuario anónimo),
        // lo redirigimos a la página de login.
        // `IS_AUTHENTICATED_FULLY` verifica que el usuario esté completamente autenticado (no "remember me" ni anónimo).
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $loginUrl = $this->urlGenerator->generate('app_sesiones_login'); // Usa tu ruta de login
            $response = new RedirectResponse($loginUrl);
            $event->setResponse($response);
            return;
        }

        // Si el usuario *está* autenticado pero no tiene permisos,
        // lo redirigimos a la página de inicio o a una página de "acceso denegado" personalizada.
        // Puedes cambiar 'app_homepage' por la ruta que desees.
        $redirectUrl = $this->urlGenerator->generate('app_sesiones_login'); // Asume que tienes una ruta 'app_homepage'
        $response = new RedirectResponse($redirectUrl);
        $event->setResponse($response);
    }
}