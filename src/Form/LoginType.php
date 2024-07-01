<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'data' => $options['last_username'],
                'required' => true
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => true
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label' => "Se souvenir de moi",
                'required' => false,
            ])
            ->add('login', SubmitType::class, [
                'label' => 'Se connecter'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'csrf_field_name' => '_csrf_token',
                'csrf_token_id' => 'authenticate'
            ])
            ->setRequired('last_username')
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
