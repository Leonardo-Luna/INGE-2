<?php

namespace App\Controller;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Form\RegistrarClienteType;
use App\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UsuariosController extends AbstractController
{
    public function __construct(private RolRepository $rolesRepository,
                                private EntityManagerInterface $manager) { }

    #[Route('/usuarios/nuevo-cliente', name: 'app_usuarios_nuevo_cliente')]
    public function NuevoCliente(Request $request): Response
    {

        $nuevoUsuario = new Usuario();
        $form = $this->createForm(RegistrarClienteType::class, $nuevoUsuario);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  
            $rolAutenticado = $this->rolesRepository->find(Rol::AUTENTICADO);
            $rolCliente = $this->rolesRepository->find(Rol::CLIENTE);

            $nuevoUsuario->addRole($rolAutenticado);
            $nuevoUsuario->addRole($rolCliente);

            $randomPassword = $this->generateRandomString();
            $hashedPassword = hash('sha256', $randomPassword);
            $nuevoUsuario->setPassword($hashedPassword);

            // ACÁ VA LA LÓGICA DE ENVÍO DE LA CONTRASEÑA AL CORREO ELECTRÓNICO ASIGNADO (sería enviar la randomPassword)

            $this->manager->persist($nuevoUsuario);
            $this->manager->flush();

            $this->addFlash('success', 'Usuario creado exitosamente.');
            return $this->redirectToRoute('app_usuarios_nuevo_cliente');
        }

        return $this->render('usuarios/registrar-cliente.html.twig', [
            'form' => $form,
        ]);
    }

    private function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }
}
