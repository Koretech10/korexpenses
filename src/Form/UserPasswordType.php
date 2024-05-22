<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required' => true,
                'first_options'  => [
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
                ],
                'second_options' => [
                    'label' => 'Répéter mot de passe'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }
}
