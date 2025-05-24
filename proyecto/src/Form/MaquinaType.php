<?php

namespace App\Form;

use App\Entity\Maquina;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; 

class MaquinaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Nombre')
            ->add('Marca')
            ->add('costoPorDIa')
            ->add('Descripcion')
            ->add('enReparacion')
            ->add('Anio') 
            ->add('minimoDias')
            ->add('Tipo')
            ->add('rutaImagen')
            ->add('reembolsoNormal')
            ->add('diasReembolso')
            ->add('reembolsoPenalizado')

            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maquina::class,
        ]);
    }
}