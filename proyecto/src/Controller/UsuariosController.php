<?php

namespace App\Controller;

use App\Entity\EstadoReserva;
use App\Entity\Rol;
use App\Entity\User;
use App\Form\EditarUsuarioType;
use App\Form\RegistrarClienteType;
use App\Form\EditarMiUsuarioType;
use App\Form\RegistrarEmpleadoType;
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

            $verificarExistencia = $this->manager->getRepository(User::class)->findOneUniqueUser($nuevoUsuario->getDni(), $rolCliente->getNombre(), $nuevoUsuario->getEmail());
            
            if($verificarExistencia->getEmail() == $nuevoUsuario->getEmail()) {
                $this->addFlash('error', 'El correo electrónico ya se encuentra registrado en el sistema.');
                return $this->redirectToRoute('app_usuarios_nuevo_cliente');       
            }

            if($verificarExistencia->getDni() == $nuevoUsuario->getDni()) {
                $this->addFlash('error', 'El DNI ya se encuentra registrado en el sistema.');
                return $this->redirectToRoute('app_usuarios_nuevo_cliente');       
            }

            $nacimiento = $nuevoUsuario->getFechaNacimiento();
            $hoy = new \DateTime();
            $edad = $hoy->diff($nacimiento)->y;
            if ($edad < 18) {
                $this->addFlash('error', 'No se pudo registrar al cliente, debe ser mayor de 18 años para tener una cuenta en el sistema.');
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

            $this->addFlash('success', 'Cuenta creada exitosamente.');
            return $this->redirectToRoute('app_usuarios_nuevo_cliente');
        }

        return $this->render('usuarios/registrar-cliente.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/gerencia/usuarios/nuevo-empleado', name: 'app_usuarios_nuevo_empleado')]
    public function NuevoEmpleado(Request $request): Response
    {
        $nuevoUsuario = new User();
        $form = $this->createForm(RegistrarEmpleadoType::class, $nuevoUsuario);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  
            $rolAutenticado = $this->rolesRepository->find(Rol::AUTENTICADO);
            $rolEmpleado = $this->rolesRepository->find(Rol::EMPLEADO);

            $verificarExistencia = $this->manager->getRepository(User::class)->findOneBy(['email' => $nuevoUsuario->getEmail()]);

            if($verificarExistencia) {
                $this->addFlash('error', 'El correo electrónico ya se encuentra registrado en el sistema.');
                return $this->redirectToRoute('app_usuarios_nuevo_empleado');       
            }

            $nacimiento = $nuevoUsuario->getFechaNacimiento();
            $hoy = new \DateTime();
            $edad = $hoy->diff($nacimiento)->y;
            if ($edad < 18) {
                $this->addFlash('error', 'No se pudo registrar al cliente, debe ser mayor de 18 años para tener una cuenta en el sistema.');
                return $this->redirectToRoute('app_usuarios_nuevo_empleado');
            }

            $nuevoUsuario->addRole($rolAutenticado->getNombre());
            $nuevoUsuario->addRole($rolEmpleado->getNombre());

            $randomPassword = $this->stringService->generateRandomPassword();
            $hashedPassword = $this->passwordHasher->hashPassword($nuevoUsuario, $randomPassword);
            $nuevoUsuario->setPassword($hashedPassword);

            $this->mailService->enviarPassword($randomPassword, $nuevoUsuario->getEmail());

            $this->manager->persist($nuevoUsuario);
            $this->manager->flush();

            $this->addFlash('success', 'Cuenta creada exitosamente.');
            return $this->redirectToRoute('app_usuarios_nuevo_empleado');
        }

        return $this->render('usuarios/registrar-empleado.html.twig', [
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

            $this->addFlash('success', 'Cuenta eliminada exitosamente.');
        }
        else { # ID no existente
            $this->addFlash('error', 'Se produjo un error al eliminar el usuario');
        }

        return $this->redirectToRoute('app_usuarios');
    
    }

    #[Route('/administracion/lista-usuarios', name: 'app_lista_usuarios')]
    public function listarUsuarios(Request $request): Response
    {
        $users = $this->manager->getRepository(User::class)->findActiveUsers();

        return $this->render('usuarios/listar-todos.html.twig', [
            "users" => $users,
        ]);
    }

    #[Route('/mi-perfil/editar', name: 'app_editar_mi_perfil')]
    public function EditarPerfilPropio(Request $request): Response
    {
        $user = $this->getUser();

        if(!$user) {
            $this->addFlash('error', 'Debe iniciar sesión para editar su perfil.');
            return $this->redirectToRoute('app_login'); 
        }

        if($user->isEliminado()) {
            $this->addFlash('error', 'El usuario se encuentra eliminado y no puede ser editado.');
            return $this->redirectToRoute('app_login'); 
        }

        #Si el usuario es gerente, no puede editar
        $rolGerente = $this->rolesRepository->find(Rol::GERENTE);
        if(in_array($rolGerente->getNombre(), $user->getRoles())) {
            $this->addFlash('error', 'No puede editar un usuario con rol de gerente.');
            return $this->redirectToRoute('app_perfil'); 
        }

        $form = $this->createForm(EditarMiUsuarioType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $verificarExistencia = $this->manager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if($verificarExistencia && $verificarExistencia->getId() != $user->getId()) { # Si existe otro usuario con el mismo email y no es el mismo usuario
                $this->addFlash('error', 'El correo electrónico ya se encuentra registrado.');
                return $this->redirectToRoute('app_editar_mi_perfil'); 
            }
            $this->manager->flush();
            $this->addFlash('success', 'Perfil editado exitosamente.');
            return $this->redirectToRoute('app_perfil'); 
        }

        return $this->render('usuarios/editar-mi-perfil.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
  
    #[Route('/administracion/usuarios/editar/{id}', name: 'app_editar_usuario')]
    public function EditarUsuario(int $id, Request $request): Response
    {
        $user = $this->manager->getRepository(User::class)->find($id);

        if(!$user) {
            $this->addFlash('error', 'El usuario no existe.');
            return $this->redirectToRoute('app_lista_usuarios'); 
        }

        if($user->isEliminado()) {
            $this->addFlash('error', 'El usuario se encuentra eliminado y no puede ser editado.');
            return $this->redirectToRoute('app_lista_usuarios'); 
        }

        #Si el usuario es gerente, no puede editar
        $rolGerente = $this->rolesRepository->find(Rol::GERENTE);
        if(in_array($rolGerente->getNombre(), $user->getRoles())) {
            $this->addFlash('error', 'No puede editar un usuario con rol de gerente.');
            return $this->redirectToRoute('app_lista_usuarios'); 
        }

        $form = $this->createForm(EditarUsuarioType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $verificarExistencia = $this->manager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if($verificarExistencia&& $verificarExistencia->getId() != $id) { # Si existe otro usuario con el mismo email y no es el mismo usuario
                $this->addFlash('error', 'El correo electrónico ya se encuentra registrado.');
                return $this->redirectToRoute('app_editar_usuario', ['id' => $id]); 
            }
            $nacimiento = $user->getFechaNacimiento();
            $hoy = new \DateTime();
            $edad = $hoy->diff($nacimiento)->y;
            if ($edad < 18) {
                $this->addFlash('error', 'No se pudo editar la fecha de nacimiento del usuario, debe ser mayor de 18 años para tener una cuenta en el sistema.');
                return $this->redirectToRoute('app_editar_usuario', ['id' => $id]);
            }
            $this->manager->flush();
            $this->addFlash('success', 'Usuario editado exitosamente.');
            return $this->redirectToRoute('app_editar_usuarios'); 
        }

        return $this->render('usuarios/editar-cliente.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}