<?php

namespace App\Form;

use App\Entity\Maquina;
use App\Entity\Sucursal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; 
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\Validator\Constraints\File;       
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Count;

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
            ->add('imagenes', FileType::class, [ 
                'label' => 'Imágenes de la Máquina (JPG, PNG, GIF)',
                'multiple' => true, 
                'mapped' => false,
                'required' => false, 
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'Debe subir al menos una imagen.',
                    ]),
                    new All([
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                            ],
                            'mimeTypesMessage' => 'Por favor, suba imágenes válidas (JPG, PNG o GIF).',
                        ])
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*' 
                ],
            ])

               ->add('ubicacion', EntityType::class, [
                'class' => Sucursal::class,
                'choice_label' => 'nombre', // Muestra el nombre de la sucursal en el select
                'placeholder' => 'Seleccione una sucursal', // Opcional: texto predeterminado
                'required' => true, 
            ])
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