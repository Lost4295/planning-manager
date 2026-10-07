<?php

namespace App\Form;

use App\Entity\Date;
use App\Enum\RepeatableEnum;
use App\Service\RecurringDateGenerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('important', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input'],
                'required' => false,
            ])
            ->add('start_date', DateTimeType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('end_date', DateTimeType::class, [
                'attr' => ['class' => 'form-control']
            ]);

        if ($options['allow_repeat']) {
            $builder
                ->add('repeat_every', ChoiceType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Répéter',
                    'placeholder' => 'Ne pas répéter',
                    'choices' => RepeatableEnum::cases(),
                    'choice_label' => fn (RepeatableEnum $e) => $e->label(),
                    'choice_value' => fn (?RepeatableEnum $e) => $e?->value,
                    'attr' => ['class' => 'form-control'],
                ])
                ->add('repeat_until', DateType::class, [
                    'mapped' => false,
                    'required' => false,
                    'widget' => 'single_text',
                    'label' => 'Répéter jusqu\'au',
                    'attr' => ['class' => 'form-control'],
                ])
                ->add('repeat_count', IntegerType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Ou nombre total d\'occurrences',
                    'attr' => ['class' => 'form-control', 'min' => 2, 'max' => RecurringDateGenerator::MAX_OCCURRENCES],
                ])
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                    $form = $event->getForm();
                    if (null === $form->get('repeat_every')->getData()) {
                        return;
                    }
                    $until = $form->get('repeat_until')->getData();
                    $count = $form->get('repeat_count')->getData();
                    if ((null === $until) === (null === $count)) {
                        $form->get('repeat_count')->addError(new FormError('Indiquez soit une date de fin, soit un nombre d\'occurrences (pas les deux).'));
                        return;
                    }
                    $start = $form->get('start_date')->getData();
                    if (null !== $until && $start && $until->format('Y-m-d') <= $start->format('Y-m-d')) {
                        $form->get('repeat_until')->addError(new FormError('La date de fin doit être postérieure au début.'));
                    }
                    if (null !== $count && ($count < 2 || $count > RecurringDateGenerator::MAX_OCCURRENCES)) {
                        $form->get('repeat_count')->addError(new FormError('Le nombre d\'occurrences doit être entre 2 et ' . RecurringDateGenerator::MAX_OCCURRENCES . '.'));
                    }
                });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Date::class,
            'allow_repeat' => true,
        ]);
    }
}
