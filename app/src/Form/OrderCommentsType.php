<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Section;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderCommentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('custom_infos', TextType::class, [
                'required' => false,
                'label' => 'Commentaires',
                'attr' => [
                    'placeholder' => 'Ex: Pas de fromage svp',
                ],
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Valider',
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
            'data_class' => Order::class
        ]);
    }
}
