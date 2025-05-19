<?php

namespace App\Controller;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Services\StringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SesionesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,
                                private StringService $stringService) { }

    #[Route('/login', name: 'app_sesiones')]
    public function login(Request $request): Response
    {

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('email', TextType::class)
            ->add('password', TextType::class)
        ;

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();
            $existeUsuario = $this->manager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
            $rolGerente = $this->manager->getRepository(Rol::class)->find(Rol::GERENTE);

            if($existeUsuario) {
                // To-do:
                    # Hashear la contraseña ingresada y compararla con la del usuario existente
                    # Manejar la lógica de las sesiones, debería quedarse iniciada 🤓 

                # Eso de arriba es lógica comaprtida, vean cómo lo hacen entre ustedes, caguense a piñas

                if($existeUsuario->hasRole($rolGerente)) {
                    // Lógica para el inicio de gerente
                    // Asumo que lo mejor sería hacer una redirección a la página de chequeo de 2FA, manejalo como quieras don Santi
                   
                    var_dump("Gerente"); die();
                }
                else {
                    var_dump("Usuario normal"); die();
                    // Lógica para el inicio normal
                    // Acá no tengo ni idea de qué es lo mejor para hacer, éxitos en tu journey Mati 
                }
            }
            else {
                # Y acá tiramos un flash de error de credenciales or something like that, dejo un ejemplo por si la quieren usar, solo es acomodarla
                /*
                $this->addFlash('success', 'Usuario creado exitosamente.');
                return $this->redirectToRoute('app_usuarios_nuevo_cliente');
            */
            }
        }

        return $this->render('sesiones/login.html.twig', [
            'form' => $form,
        ]);
    }
}
