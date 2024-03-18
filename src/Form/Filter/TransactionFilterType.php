<?php

namespace App\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('dateTo', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('description', TextType::class, [
                'label' => 'Libellé',
                'required' => false
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Débit' => 0,
                    'Crédit' => 1
                ],
                'placeholder' => 'Sans préférence',
                'empty_data' => '',
                'required' => false
            ])
            ->add('valueFrom', MoneyType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('valueTo', MoneyType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
            ])
        ;
    }
}
