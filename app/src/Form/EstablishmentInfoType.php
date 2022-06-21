<?php

namespace App\Form;

use App\Entity\Establishment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstablishmentInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => false,
                'data' => $options['email']
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom'
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
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ],
                'data' => $options['street']
            ])
            ->add('zipcode', TextType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Adresse postale',
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ],
                'data' => $options['zipcode']
            ])
            ->add('city', TextType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control text-center border-b-2 border-t-0 border-r-0 border-l-0 border-toukan mb-10 p-2 w-full',
                ],
                'data' => $options['city']
            ])

            ->add('phone', TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Establishment::class,
            'email' => null,
            'street' => null,
            'zipcode' => null,
            'city' => null
        ]);
    }
}
