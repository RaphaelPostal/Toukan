<?php

namespace App\Form;

use App\Entity\Establishment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstablishmentPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false,
            ])

                ->add('newPassword', RepeatedType::class, [
                    'mapped'=>false,
                    'type' => PasswordType::class,
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options'  => [
                        'label' => 'Nouveau mot de passe',
                        'attr'=>[
                            'class' => 'form-text',
                        ]
                    ],
                    'second_options' => [
                        'label' => 'Confirmation du nouveau mot de passe',
                        'attr'=>[
                            'class' => 'form-text',
                        ]
                    ],
                ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer'
            ]);
    }
}
