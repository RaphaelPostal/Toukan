<?php

namespace App\Form;

use App\Entity\Sauce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SauceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title')
        ->add('available', CheckboxType::class, [
            'label' => 'Disponible',
            'required' => false,
        ])
        ->add('save', SubmitType::class, [
            'label' => $options['save-label']
            ]
        );
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sauce::class,
            'save-label' => null,
        ]);
    }
}
