<?php

namespace App\Form;

use App\Entity\Date;
use App\Enum\RepeatableEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,[
                'attr'=>['class'=>'form-control']
            ])
            ->add('description', TextareaType::class,[
                'attr'=>['class'=>'form-control']
            ])
            ->add('start_date', DateTimeType::class,[
                'attr'=>['class'=>'form-control']
            ])
            ->add('end_date', DateTimeType::class,[
                'attr'=>['class'=>'form-control']
            ])
            ->add('color', ColorType::class,[
                'attr'=>['class'=>'form-control form-control-color']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Date::class,
        ]);
    }
}
