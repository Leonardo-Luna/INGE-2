<?php

namespace App\Controller;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Form\RegistrarClienteType;
use App\Repository\RolRepository;
use App\Services\MailService;
use App\Services\StringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UsuariosController extends AbstractController
{
    public function __construct(private RolRepository $rolesRepository,
                                private EntityManagerInterface $manager,
                                private MailService $mailService,
                                private StringService $stringService) { }

    #[Route('/usuarios/nuevo-cliente', name: 'app_usuarios_nuevo_cliente')]
    public function NuevoCliente(Request $request): Response
    {

        $nuevoUsuario = new Usuario();
        $form = $this->createForm(RegistrarClienteType::class, $nuevoUsuario);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  
            $rolAutenticado = $this->rolesRepository->find(Rol::AUTENTICADO);
            $rolCliente = $this->rolesRepository->find(Rol::CLIENTE);

            $verificarExistencia = $this->manager->getRepository(Usuario::class)->findOneBy(['email' => $nuevoUsuario->getEmail()]);

            if($verificarExistencia) {
                $this->addFlash('error', 'El correo electrónico ya se encuentra registrado.');
                return $this->redirectToRoute('app_usuarios_nuevo_cliente');       
            }

            $nuevoUsuario->addRole($rolAutenticado);
            $nuevoUsuario->addRole($rolCliente);

            $randomPassword = $this->stringService->generateRandomPassword();
            $hashedPassword = hash('sha256', $randomPassword);
            $nuevoUsuario->setPassword($hashedPassword);

            // ACÁ VA LA LÓGICA DE ENVÍO DE LA CONTRASEÑA AL CORREO ELECTRÓNICO ASIGNADO (sería enviar la randomPassword)
            $this->mailService->enviarPassword($randomPassword, $nuevoUsuario->getEmail());

            $this->manager->persist($nuevoUsuario);
            $this->manager->flush();

            $this->addFlash('success', 'Usuario creado exitosamente.');
            return $this->redirectToRoute('app_usuarios_nuevo_cliente');
        }

        return $this->render('usuarios/registrar-cliente.html.twig', [
            'form' => $form,
        ]);
    }

}
