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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
            ->add('rutaImagen', FileType::class, [ // <<<<<<<< Cambiado a FileType
                'label' => 'Imagen de la Máquina (JPG, PNG, GIF)',
                'mapped' => false, // <<<<<<<< IMPORTANTE: esto no se mapea directamente a la entidad
                'required' => false, // Puedes hacerlo obligatorio o no
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Límite de tamaño, ej. 5 megabytes
                        'mimeTypes' => [    // Tipos de archivo permitidos
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Por favor, suba una imagen válida (JPG, PNG o GIF).',
                    ])
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