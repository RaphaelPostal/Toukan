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
            ->add('name', TextType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'choices' => [
                    'Restaurant' => 'restaurant',
                    'Bar' => 'bar',
                    'Bar/Restaurant' => 'bar-restaurant',
                ],
            ])
            ->add('address', TextType::class, [
                'mapped' => false,
                'required' => true,
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
            ])
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Veuillez accepter nos conditions d\'utilisation.',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => true,
                'type' => PasswordType::class,
                'attr' => ['autocomplete' => 'new-password'],
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
