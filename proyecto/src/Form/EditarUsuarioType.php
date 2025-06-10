<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditarUsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];

        $builder
            ->add('email', EmailType::class, [
                'required' => false,
                'empty_data' => $user->getEmail(),
            ])
            ->add('dni', TextType::class, [
                'required' => false,
                'empty_data' => $user->getDni(),
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