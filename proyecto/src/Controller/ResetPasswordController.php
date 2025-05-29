<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserToken;
use App\Services\StringService;
use App\Services\MailService;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordConfirmType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final class ResetPasswordController extends AbstractController
{
    public function __construct( private StringService $stringService,
                                 private MailService $mailService,
                                 private EntityManagerInterface $manager,
                                 private UserPasswordHasherInterface $hasher) { }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);          // Creamos el formulario
        $form->handleRequest($request);                               // Obtenemos los datos del formulario
        if($form->isSubmitted() && $form->isValid()) { 
            $email=$form->get('email')->getData();                    //Obtenemos el email del formulario
            $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $email]); // Buscamos el usuario por el email
            if ($user) {                                              // Si existe el usuario     
                $this->sendToken($user);                              // Enviamos el token al usuario                      
                $this->addFlash('success', 'Se envio un mail a la direccion de correo electronico con instrucciones para recuperar tu contraseña.'); // Mensaje de éxito
            }else{
                $this->addFlash('error', 'No se encontro un usuario con la direccion de correo ingresada.'); // Si no existe el usuario, mostramos un mensaje de error
            }
        }
        return $this->render('reset-password/request.html.twig', [ 
            'form' => $form->createView(), // Renderizamos el formulario
            'controller_name' => 'ResetPasswordController',
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password_confirm')]
    public function reset(Request $request): Response
    {
        $tokenValue = $request->attributes->get('token'); // Obtenemos el token de la URL 
        $userToken = $this->manager->getRepository(UserToken::class)->findOneBy(['token' => $tokenValue]); // Buscamos el usuario por el token
        
        if (!$userToken) { // Si no existe el token
            $this->addFlash('error', 'El token de recuperación es inválido o ha expirado.');
            return $this->redirectToRoute('app_reset_password');
        }

        $user = $this->manager->getRepository(User::class)->findOneBy(['resetToken' => $userToken]); // Buscamos el usuario por el token
        
        if(!$user) { 
            $this->addFlash('error', 'El usuario asociado al token no existe.');
            return $this->redirectToRoute('app_reset_password');
        }

        if($user->getResetToken()->getCreatedAt() < (new DateTime())->modify('-30 minutes')) { // Verificamos si el token ha expirado (1 hora)
            $this->addFlash('error', 'El token de recuperación ha expirado.');
            return $this->redirectToRoute('app_reset_password');
        }

        $form = $this->createForm(ResetPasswordConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { // Si el formulario es enviado y es válido
            $email = $user->getEmail(); // Obtenemos el email del usuario
            if ($email !== $form->get('email')->getData()) { // Verificamos que el email del formulario coincida con el del usuario
                $this->addFlash('error', 'El correo electrónico no coincide con el del usuario.');
                return $this->redirectToRoute('app_reset_password_confirm', ['token' => $tokenValue]);
            }
            $newPassword = $form->get('newPassword')->getData(); // Obtenemos la nueva contraseña del formulario
            $hashedPassword = $this->hasher->hashPassword($user, $newPassword); // Hasheamos la nueva contraseña
            $user->setPassword($hashedPassword); // Seteamos la nueva contraseña al usuario
            $user->setResetToken(null); // Eliminamos el token de recuperación del usuario
            $this->manager->flush(); // Guardamos los cambios en la base de datos

            $this->addFlash('success', 'Tu contraseña ha sido actualizada exitosamente.'); // Mensaje de éxito
            return $this->redirectToRoute('app_sesiones_login'); // Redirigimos al login
        }

        return $this->render('reset-password/confirm.html.twig', [
            'form' => $form->createView(), // Renderizamos el formulario
            'controller_name' => 'ResetPasswordController',
        ]);
    }

    public function sendToken($user){
        $code = $this->stringService->generateToken(); // Generamos un código aleatorio de 32 caracteres
        $to= $user->getEmail();

        $resetToken = new UserToken();
        $resetToken->setToken($code);
        $resetToken->setCreatedAt(new DateTime());

        $user->setResetToken($resetToken);
        $this->manager->flush();

        $link = $this->generateUrl('app_reset_password_confirm', ['token' => $code], UrlGeneratorInterface::ABSOLUTE_URL); // Generamos el link de reset
        $this->mailService->EnviarRecuperarContraseña($link, $to); 
    }
}
