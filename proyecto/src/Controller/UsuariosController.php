<?php

namespace App\Controller;

use App\Entity\EstadoReserva;
use App\Entity\Rol;
use App\Entity\User;
use App\Form\RegistrarClienteType;
use App\Repository\RolRepository;
use App\Services\MailService;
use App\Services\StringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UsuariosController extends AbstractController
{
    public function __construct(private RolRepository $rolesRepository,
                                private EntityManagerInterface $manager,
                                private MailService $mailService,
                                private StringService $stringService,
                                private UserPasswordHasherInterface $passwordHasher) { }

    #[Route('/administracion/usuarios/nuevo-cliente', name: 'app_usuarios_nuevo_cliente')]
    public function NuevoCliente(Request $request): Response
    {

        $nuevoUsuario = new User();
        $form = $this->createForm(RegistrarClienteType::class, $nuevoUsuario);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  
            $rolAutenticado = $this->rolesRepository->find(Rol::AUTENTICADO);
            $rolCliente = $this->rolesRepository->find(Rol::CLIENTE);

            $verificarExistencia = $this->manager->getRepository(User::class)->findOneBy(['email' => $nuevoUsuario->getEmail()]);

            if($verificarExistencia) {
                $this->addFlash('error', 'El correo electrónico ya se encuentra registrado.');
                return $this->redirectToRoute('app_usuarios_nuevo_cliente');       
            }

            $nuevoUsuario->addRole($rolAutenticado->getNombre());
            $nuevoUsuario->addRole($rolCliente->getNombre());

            $randomPassword = $this->stringService->generateRandomPassword();
            $hashedPassword = $this->passwordHasher->hashPassword($nuevoUsuario, $randomPassword);
            $nuevoUsuario->setPassword($hashedPassword);

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


    #[Route('/administracion/usuarios/{id}', name: 'app_visualizar_cliente')]
    public function VisualizarCliente(int $id): Response
    {   
        $user = $this->manager->getRepository(User::class)->find($id);

        return $this->render('usuarios/visualizar-cliente.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/administracion/usuarios/eliminar/{id}', name: 'app_eliminar_usuario')]
    public function eliminarUsuario(int $id): Response
    {
        $user = $this->manager->getRepository(User::class)->find($id);

        if($user->getId() == $this->getUser()->getId()) { # Usuario propio
            $this->addFlash('error', 'No puede elimianr tu propio usuario.'); # No nos lo pidieron, pero creo que corresponde?
            return $this->redirectToRoute('app_usuarios'); 
        }

        if($user->isEliminado()) { # Usuario ya eliminado
            $this->addFlash('success', 'El usuario ya se encuentra eliminado.');
            return $this->redirectToRoute('app_usuarios'); 
        }
        if($user) {
            $reservas = $user->getReservas();
            $estadoCancelada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA)->getEstado();
            $estadoAprobada = $this->manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA)->getEstado();
            $user->setEliminado(true);

            foreach($reservas as $reserva) {
                if($reserva->getEstado() != $estadoAprobada) {
                    $reserva->setEstado($estadoCancelada);
                    $this->mailService->EnviarCancelacionSinCuponYSinPolitica($reserva->getMaquina()->getNombre(), $reserva->getCostoTotal(), $user->getEmail());
                }
            }

            $this->manager->flush();

            $this->addFlash('success', 'Usuario eliminado exitosamente.');
        }
        else { # ID no existente
            $this->addFlash('error', 'Se produjo un error al eliminar el usuario');
        }

        return $this->redirectToRoute('app_usuarios');
    
    }

    #[Route('/administracion/usuarios', name: 'app_usuarios')]
    public function listarUsuarios(Request $request): Response
    {
        $users = $this->manager->getRepository(User::class)->findActiveUsers();

        return $this->render('usuarios/listar-todos.html.twig', [
            "users" => $users,
        ]);
    }

}  
