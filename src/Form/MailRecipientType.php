<?php

namespace App\Form;

use App\Entity\MailRecipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailRecipientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Adresse e-mail', 'attr' => ['class' => 'form-control']])
            ->add('name', TextType::class, ['label' => 'Nom (optionnel)', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('daily', CheckboxType::class, ['label' => 'Récapitulatif quotidien (7h)', 'required' => false, 'attr' => ['class' => 'form-check-input']])
            ->add('weekly', CheckboxType::class, ['label' => 'Récapitulatif hebdomadaire (lundi 7h)', 'required' => false, 'attr' => ['class' => 'form-check-input']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MailRecipient::class]);
    }
}