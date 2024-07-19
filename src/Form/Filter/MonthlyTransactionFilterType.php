<?php

namespace App\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class MonthlyTransactionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $daysRange = range(1, 31);

        $builder
            ->setMethod(Request::METHOD_GET)
            ->add('dayFrom', ChoiceType::class, [
                'label' => false,
                'choices' => array_combine($daysRange, $daysRange),
                'required' => false,
            ])
            ->add('dayTo', ChoiceType::class, [
                'label' => false,
                'choices' => array_combine($daysRange, $daysRange),
                'required' => false,
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
                'label' => 'Filtrer',
            ])
        ;
    }
}
