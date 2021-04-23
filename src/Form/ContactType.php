<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', TextType::class,[
                'label' => "Prénom",
                'attr' => [
                    'placeholder' => "Merci de saisir votre prénom",
                ]
            ])
            ->add('nom', TextType::class,[
                'label' => "Nom",
                'attr' => [
                    'placeholder' => "Merci de saisir votre nom",
                ]
            ])
            ->add('email', EmailType::class,[
                'label' => "Email",
                'attr' => [
                    'placeholder' => "Merci de saisir votre email",
                ]
            ])
            ->add('content', TextareaType::class,[
                'label' => "Votre message",
                'attr' => [
                    'placeholder' => "En quoi pouvons-nous vous aider ?",
                ]
            ])
            ->add('submit', SubmitType::class,[
                'label' => "Envoyer",
                "attr" => [
                    "class" => "btn-block btn-info"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
