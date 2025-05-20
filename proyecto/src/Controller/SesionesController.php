<?php

namespace App\Controller;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Services\MailService;
use App\Services\SesionesService;
use App\Services\StringService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SesionesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,
                                private StringService $stringService,
                                private MailService $mailService,
                                private SesionesService $sesionesService) { }

    #[Route('/login', name: 'app_sesiones_login')]
    public function login(Request $request): Response
    {

        $formBuilder = $this->createFormBuilder(); # Armo un formulario on the fly 🕊 
        $formBuilder
            ->add('email', TextType::class)
            ->add('password', TextType::class)
        ;

        $form = $formBuilder->getForm(); # Lo convierto a un formulario renderizable
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();
            $hashedPassword = hash('sha256', $password);
            $existeUsuario = $this->manager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
            $rolGerente = $this->manager->getRepository(Rol::class)->find(Rol::GERENTE);

            if($existeUsuario) {

                if($existeUsuario->getPassword() == $hashedPassword) {

                    if($existeUsuario->hasRole($rolGerente)) { # Si es gerente:
                        $this->EnviarToken($existeUsuario);
                        $this->redirectToRoute('app_sesiones_token', ['id' => $existeUsuario->getId()]); # Agregar parámetro de la ID, no me acuerdo como era $existeUsuario->getId()
                    }
                    else { # Si es usuario normal:
                        $this->sesionesService->iniciarSesion(); # ESTE METODO ESTA VACIO, FALTA IMPLEMENTARLO @MATI, nunca implementé sesiones de 0 en Symfony, suerte :D
                    }
                }
                else { # Contraseña incorrecta ### YO DIFIERO DE ESTO. UN SOLO ESCENARIO POR CREDENCIALES, PERO ASÍ ESTÁ LA HU
                    $this->addFlash('error', 'La contraseña ingresada es incorrecta.');
                    return $this->redirectToRoute('app_sesiones_login');
                }
            }
            else { # Si no existe la cuenta:
                $this->addFlash('error', 'El correo electrónico ingresado no se encuentra registrado en el sistema.');
                return $this->redirectToRoute('app_sesiones_login');
            }
        }

        return $this->render('sesiones/login.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/login/token/{id}', name: 'app_sesiones_token')]
    public function ComprobarToken(Request $request, $id) {

        $usuario = $this->manager->getRepository(Usuario::class)->findOneBy(['id' => $id]);
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
                    return $this->redirectToRoute('app_sesiones_token', ['id' => $id]); # POR AHORA NO APLICA, ESPEREMOS A QUE NOS DEJEN CAMBIAR LA HU
                }
                else { # Éxito
                    $this->sesionesService->iniciarSesion();
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
