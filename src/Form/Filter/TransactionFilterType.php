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
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class TransactionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setAction($options['target_url'])
            ->setMethod('GET')
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
                'empty_data' => [],
                'required' => false
            ])
            ->add('valueFrom', MoneyType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [new PositiveOrZero()]
            ])
            ->add('valueTo', MoneyType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [new PositiveOrZero()]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('target_url')
        ;
    }
}
