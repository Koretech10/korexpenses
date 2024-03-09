<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transaction = $builder->getData();

        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text'
            ])
            ->add('description', TextType::class, [
                'label' => 'Libellé'
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'multiple' => false,
                'expanded' => true,
                'choices' => [
                    'Débit' => 0,
                    'Crédit' => 1
                ],
                // Choix par défaut à la création uniquement
                'data' => $transaction->getType() ?? 0
            ])
            ->add('value', MoneyType::class, [
                'label' => 'Valeur'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class
        ]);
    }
}
