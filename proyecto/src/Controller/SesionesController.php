<?php

namespace App\Controller;

use App\Services\MailService;
use App\Services\SesionesService;
use App\Services\StringService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SesionesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,
                                private MailService $mailService,
                                private SesionesService $sesionesService,
                                private AuthorizationCheckerInterface $authChecker,
                                private StringService $stringService) { }

    #[Route('/verificar', name: 'app_sesiones_verificar')]
    public function VerificarGerente(Request $request) {
        if($this->authChecker->isGranted('ROLE_GERENTE')) {
            $usuario = $this->getUser();

            $code = $this->stringService->generate2FA();
            $to = $usuario->getEmail();

            $usuario->setToken2FA($code); # Se setea el código contra el cual comparar
            $usuario->setExpiracion2FA(new DateTime()); # Se setea el tiempo actual para ver que no esté expirado
            $this->manager->flush();
            # $this->mailService->Enviar2FA($code, $to); # Este método existe y funciona, pero para no mandar 1000 mails mejor dejarlo así a menos que se quiera probar :D
        
            $this->redirectToRoute('app_sesiones_token', ['id' => $usuario->getId()]);
        }

        $this->redirectToRoute('app_index');

    }

    // #[Route('/login', name: 'app_sesiones_login')] # Lo dejo comentado por si de la nada nos sirve algo de todo esto, a futuro se borrará
    // public function login(Request $request): Response
    // {

    //     $formBuilder = $this->createFormBuilder(); # Armo un formulario on the fly 🕊 
    //     $formBuilder
    //         ->add('email', TextType::class)
    //         ->add('password', TextType::class)
    //     ;

    //     $form = $formBuilder->getForm(); # Lo convierto a un formulario renderizable
    //     $form->handleRequest($request);

    //     if($form->isSubmitted() && $form->isValid()) {

    //         $email = $form->get('email')->getData();
    //         $password = $form->get('password')->getData();
    //         $hashedPassword = hash('sha256', $password);
    //         $existeUsuario = $this->manager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
    //         $rolGerente = $this->manager->getRepository(Rol::class)->find(Rol::GERENTE);

    //         if(($existeUsuario) && ($existeUsuario->getPassword() == $hashedPassword)) {

    //             if($existeUsuario->hasRole($rolGerente)) { # Si es gernete:
    //                 $code = $this->stringService->generate2FA();
    //                 $to = $existeUsuario->getEmail();

    //                 $existeUsuario->setToken2FA($code); # Se setea el código contra el cual comparar
    //                 $existeUsuario->setExpiracion2FA(new DateTime()); # Se setea el tiempo actual para ver que no esté expirado
    //                 $this->manager->flush();
    //                 # $this->mailService->Enviar2FA($code, $to); # Este método existe y funciona, pero para no mandar 1000 mails mejor dejarlo así a menos que se quiera probar :D
    //             }
    //             else { # Si es usuario normal:
    //                 $this->sesionesService->iniciarSesion(); # ESTE METODO ESTA VACIO, FALTA IMPLEMENTARLO @MATI, nunca implementé sesiones de 0 en Symfony, suerte :D
    //             }
    //         }
    //         else { # Si no existe la cuenta:
    //             $this->addFlash('error', 'Las credenciales ingresadas son incorrectas.');
    //             return $this->redirectToRoute('app_sesiones_login');
    //         }
    //     }

    //     return $this->render('sesiones/login.html.twig', [
    //         'form' => $form,
    //     ]);
    // }
}
