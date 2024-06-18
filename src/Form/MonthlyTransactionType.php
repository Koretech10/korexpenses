<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\MonthlyTransaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthlyTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $monthlyTransaction = $builder->getData();
        $daysRange = range(1, 31);

        $builder
            ->setAction($options['target_url'])
            ->add('day', ChoiceType::class, [
                'label' => "Jour de l'occurrence",
                'help' => "Si ce jour n'existe pas dans le mois en cours, l'occurrence aura lieu au dernier jour de ce 
                           mois.",
                // La clé du tableau doit correspondre au nom du choix donc il faut utiliser 2 fois $daysRange pour
                // avoir le nom du choix et la valeur du choix
                'choices' => array_combine($daysRange, $daysRange),
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
                'data' => $monthlyTransaction->getType() ?? 0
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
            'data_class' => MonthlyTransaction::class,
            'target_url' => '',
        ]);
    }
}
