<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditarUsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];

        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'title' => 'El correo electrónico debe tener un formato válido ej.: ejemplo@hotmail.com'
                ],
                'constraints' => [
                    new Email([
                        'message' => 'El correo electrónico "{{ value }}" no es válido.',
                    ]),
                ],
                'required' => false,
                'empty_data' => $user->getEmail(),
            ])
            ->add('dni', TextType::class, [
                'required' => false,
                'empty_data' => $user->getDni(),
                'attr' => [
                 'pattern' => '\d{7,8}', // solo números, 7 u 8 dígitos
                 'title' => 'El DNI debe tener 7 u 8 números sin puntos ni letras',
                ],
            ])
            ->add('nombre', TextType::class, [
                'required' => false,
                'empty_data' => $user->getNombre(),
            ])
            ->add('apellido', TextType::class, [
                'required' => false,
                'empty_data' => $user->getApellido(),
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}