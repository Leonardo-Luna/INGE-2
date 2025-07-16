<?php

namespace App\Form;

use App\Entity\Maquina;
use App\Entity\Sucursal;
use App\Form\DataTransformer\ReembolsoToPorcentajeTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EditarMaquinaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maquina = $options['data'];

        $builder
            ->add('Nombre', TextType::class, [
                'label' => 'Nombre de la máquina',
                'required' => false,
            ])
            ->add('Marca', TextType::class, [
                'label' => 'Marca',
                'required' => false,
            ])
            ->add('costoPorDia', NumberType::class, [
                'label' => 'Costo por día ($)',
                'attr' => ['min' => 0, 'step' => '0.01'],
                'required' => false,
            ])
            ->add('Descripcion', TextType::class, [
                'label' => 'Descripción',
                'required' => false,
            ])
            ->add('Anio', IntegerType::class, [
                'label' => 'Año de fabricación',
                'attr' => ['min' => 1900, 'max' => (int)date('Y')],
                'required' => false,
            ])
            ->add('minimoDias', IntegerType::class, [
                'label' => 'Mínimo de días de alquiler',
                'attr' => ['min' => 1],
                'required' => false,
            ])
            ->add('Tipo', TextType::class, [
                'label' => 'Tipo de máquina',
                'required' => false,
            ])
            ->add('reembolsoNormal', NumberType::class, [
                'label' => 'Reembolso normal por día (%)',
                'attr' => ['min' => 0,'max' => 100, 'step' => '0.01'],
                'required' => false,
            ])
            ->add('diasReembolso', IntegerType::class, [
                'label' => 'Días para aplicar reembolso',
                'attr' => ['min' => 1],
                'required' => false,
            ])
            ->add('reembolsoPenalizado', NumberType::class, [
                'label' => 'Reembolso penalizado por día (%)',
                'attr' => ['min' => 0, 'max' => 100, 'step' => '0.01'],
                'required' => false,
            ])
            ->add('imagenes', FileType::class, [
                'label' => 'Agregar nuevas imágenes',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => ['image/*'],
                            'mimeTypesMessage' => 'Solo se permiten imágenes JPG, PNG o GIF.',
                        ])
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control'
                ]
            ])
            ->add('ubicacion', EntityType::class, [
                'label' => 'Sucursal donde se encuentra',
                'class' => Sucursal::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Seleccionar sucursal',
                'required' => true
            ]);

        // Agregamos los transformers para convertir pesos <-> porcentaje
        $builder->get('reembolsoNormal')->addModelTransformer(
            new ReembolsoToPorcentajeTransformer($maquina->getCostoPorDia())
        );

        $builder->get('reembolsoPenalizado')->addModelTransformer(
            new ReembolsoToPorcentajeTransformer($maquina->getCostoPorDia())
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maquina::class,
        ]);
    }
}