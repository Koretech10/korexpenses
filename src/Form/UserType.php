<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $builder->getData();

        $builder->add('username', TextType::class, [
            'label' => "Nom d'utilisateur"
        ]);

        if (!$user->getId()) { // En création uniquement
            $builder->add('password', PasswordType::class, [
                'label' => "Mot de passe",
                'help' => "Doit répondre aux exigences de sécurité suivantes : 12 caractères minimum, au moins une 
                       lettre minuscule, au moins une lettre MAJUSCULE, au moins un chiffre, au moins un de ces 
                       caractères spéciaux : ! @ # $ % ^ & *
                      ",
                'constraints' => [
                    new Regex([
                        'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{12,})/',
                        'message' => 'Le mot de passe ne répond pas aux exigences de sécurité'
                    ])
                ]
            ]);
        }

        // Affectation des rôles uniquement depuis certaines méthodes
        if ($options['show_roles']) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Valider'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => User::class,
                'show_roles' => true
            ])
        ;
    }
}
