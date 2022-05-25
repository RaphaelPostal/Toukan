<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ],
            ])
            ->add('name', TextType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Nom de l\'établissement',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ]
            ])
            ->add('type', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Type d\'établissement',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 pb-2.5 w-full',
                ],
                'choices' => [
                    'Restaurant' => 'restaurant',
                    'Bar' => 'bar',
                    'Bar/Restaurant' => 'bar-restaurant',
                ],
            ])
            ->add('street', TextType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Rue',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 pb-2.5 w-full',
                ],
            ])
            ->add('city', TextType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Ville',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 pb-2.5 w-full',
                ],
            ])
            ->add('zipcode', TextType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 pb-2.5 w-full',
                ],
            ])
            ->add('phone', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 10,
                        'minMessage' => 'Your phone number must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your phone number cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'mapped' => false,
                'required' => true,
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ],
            ])
            ->add('password', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => true,
                'label' => 'Mot de passe',
                'type' => PasswordType::class,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ],
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation du mot de passe'],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit avoir au moins {{ limit }} caractères.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Accepter les conditions d\'utilisation',
                'attr' => [
                    'class' => 'form-control text-center border-2 border-toukan mr-2 p-2',
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Veuillez accepter nos conditions d\'utilisation.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
