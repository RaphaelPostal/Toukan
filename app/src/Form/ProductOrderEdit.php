<?php

namespace App\Form;

use App\Entity\ProductOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOrderEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'required' => true,
                'label' => 'Quantité',
            ])

            ->add('save', SubmitType::class, [
                'label' => $options['save-label'],
                'attr' => [
                    'id' => 'btn-section-save',
                    'class' => 'btn btn-primary btn-section-save',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOrder::class,
            'save-label' => null
        ]);
    }
}
