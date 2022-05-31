<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'ex: Cheeseburger'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Plat' => Product::TYPE_PLAT,
                    'Boisson' => Product::TYPE_BOISSON,
                    'Menu' => Product::TYPE_MENU,
                ]
            ])
            ->add('price', TextType::class, [
                'label' => 'Prix',
                'attr' => [
                    'placeholder' => 'ex: 4.50'
                ]
            ])

            ->add('menuInformation', TextareaType::class, [
                'label' => 'Informations sur le menu (composition, etc ...)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: 6 Nuggets, Frite (grande ou petite)'
                ]
            ])
            ->add('ingredients', TextareaType::class, [
                'label' => 'Ingrédients',
                'attr' => [
                    'placeholder' => 'ex: Boeuf, oignons, ...'
                ]
            ])
            ->add('allergens', TextareaType::class, [
                'label' => 'Allergènes',
                'attr' => [
                    'placeholder' => 'ex: Gluten, lait, ...'
                ],
                'required' => false,
            ])
            ->add('sauce_choosable', CheckboxType::class, [
                'label' => 'Sauce aux choix',
                'required' => false,
            ])
            ->add('drink_choosable', CheckboxType::class, [
                'label' => 'Boisson aux choix',
                'required' => false,
            ])
            ->add('dessert_choosable', CheckboxType::class, [
                'label' => 'Dessert aux choix',
                'required' => false,
            ])
            ->add('available', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Sélectionnez une image de type png/jpeg',
                    ])
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['save-label'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'save-label' => null
        ]);
    }
}
