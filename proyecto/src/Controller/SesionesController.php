<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\MailService;
use App\Services\SesionesService;
use App\Services\StringService;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SesionesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,
                                private MailService $mailService,
                                private SesionesService $sesionesService,
                                private AuthorizationCheckerInterface $authChecker,
                                private StringService $stringService,
                                private RequestStack $requestStack) { }

    #[Route('/verificar', name: 'app_sesiones_verificar')]
    public function VerificarGerente() {
        if($this->authChecker->isGranted('ROLE_GERENTE')) {
            
            $usuario = $this->getUser();
            $this->EnviarToken($usuario);

            return $this->redirectToRoute('app_sesiones_token', ['id' => $usuario->getId()]);
        }

        return $this->redirectToRoute('app_index');
    }

    #[Route('/token', name: 'app_sesiones_empty_token')]
    public function EmptyToken(Request $request, $id) {
        return $this->redirectToRoute('app_sesiones_login');
    }

    #[Route('/token/{id}', name: 'app_sesiones_token')]
    public function ComprobarToken(Request $request, $id) {

        $usuario = $this->manager->getRepository(User::class)->findOneBy(['id' => $id]);
        $token = $usuario->getToken2FA();
        $expiracion = $usuario->getExpiracion2FA();

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('codigo', TextType::class)
        ;

        $form = $formBuilder->getForm(); # Lo convierto a un formulario renderizable
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $tokenIngresado = $form->get('codigo')->getData();

            if($tokenIngresado == $token) {
                $tiempoLogin = new DateTime();
                $segundosPasados = abs($tiempoLogin->getTimestamp() - $expiracion->getTimestamp());
                $minutosPasados = $segundosPasados / 60;
                if($minutosPasados > 5) { # Token expirado
                    $this->EnviarToken($usuario);
                    $this->addFlash('error', 'El token ingresado ha expirado, revisa tu casilla de correo para obtener el nuevo.');
                    return $this->redirectToRoute('app_sesiones_token', ['id' => $id]);
                }
                else { # Éxito
                    return $this->redirectToRoute('app_index');
                }
            }
            else { # Token incorrecto
                $this->addFlash('error', 'El token ingresado es incorrecto, vuelva a intentarlo.');
                return $this->redirectToRoute('app_sesiones_token', ['id' => $id]);
            }
        }

        return $this->render('sesiones/token.html.twig', [
            'form' => $form,
        ]);
    }

    private function EnviarToken($existeUsuario) {
        $code = $this->stringService->generate2FA();
        $to = $existeUsuario->getEmail();

        $existeUsuario->setToken2FA($code); # Se setea el código contra el cual comparar
        $existeUsuario->setExpiracion2FA(new DateTime()); # Se setea el tiempo actual para ver que no esté expirado
        $this->manager->flush();
        # $this->mailService->Enviar2FA($code, $to); # Este método existe y funciona, pero para no mandar 1000 mails mejor dejarlo así a menos que se quiera probar :D
    }

}
