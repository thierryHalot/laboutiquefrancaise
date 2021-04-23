<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Votre prénom',
                'constraints' => new Length([
                    "min" => 2,
                    "max" => 30
                    ]),
                'attr' => [
                    'placeholder' => 'Indiquer votre prénom !!',
                ]
            ])
            ->add('lastname', TextType::class,[
                'label' => 'Votre nom',
                'constraints' => new Length([
                    "min" => 2,
                    "max" => 30
                    ]),
                'attr' => [
                    'placeholder' => 'Indiquer votre nom !!',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'constraints' => new Length([
                    "min" => 2,
                    "max" => 60
                    ]),
                'attr' => [
                    'placeholder' => "Merci d'indiquer un email valide",
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe et la confirmation doivent être identiques',
                'required' => true,
                'first_options' => [
                'label' => "Mot de passe",
                'attr' => [
                    'placeholder' => 'Indiquer un mot de passe !!',
                ]
                ],
                'second_options' => [
                'label' => "Confirmer votre Mot de passe",
                'attr' => [
                    'placeholder' => 'Confirmer votre mot de passe',
                ]
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Inscription',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
