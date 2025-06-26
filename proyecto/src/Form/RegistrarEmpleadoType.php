<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;

class RegistrarEmpleadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('apellido')
            ->add('email', EmailType::class, [
                'attr' => [
                    'title' => 'El correo electrónico debe tener un formato válido ej.: ejemplo@hotmail.com'
                ],
                'constraints' => [
                    new Email([
                        'message' => 'El correo electrónico "{{ value }}" no es válido.',
                    ]),
                ],
            ])
            ->add('dni', TextType::class, [
                 'required' => true,
                 'attr' => [
                 'pattern' => '\d{7,8}', // solo números, 7 u 8 dígitos
                 'title' => 'El DNI debe tener 7 u 8 números sin puntos ni letras',
                ],
            ])
            ->add('fechaNacimiento', DateType::class, [
                'required' => true,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
