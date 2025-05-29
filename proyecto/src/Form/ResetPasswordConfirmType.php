<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;

class ResetPasswordConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
                'attr' => ['placeholder' => 'Ingresa tu correo electrónico'],
                'mapped' => false,
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => 'Nueva contraseña',
                    'attr' => ['placeholder' => 'Ingresa tu nueva contraseña'],
                    'constraints' => [
                        new Regex([
                             'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+.])[A-Za-z\d!@#$%^&*()_+.]{8,}$/',
                            'message' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.',
                       ]),
                ],
                ],
                'second_options' => [
                    'label' => 'Confirmar contraseña',
                    'attr' => ['placeholder' => 'Confirma tu nueva contraseña'],
                ],
                'invalid_message' => 'Las contraseñas no coinciden.',
                'required' => true,
                'mapped' => false, // porque probablemente luego setees la contraseña manualmente
                ],
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}