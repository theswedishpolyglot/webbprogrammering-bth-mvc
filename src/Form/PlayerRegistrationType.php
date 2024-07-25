<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PlayerRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        unset($options);

        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your name']),
                ],
                'attr' => [
                    'style' => 'margin-bottom: 10px; padding: 5px;'
                ],
                'label_attr' => [
                    'style' => 'margin-right: 10px;'
                ]
            ])
            ->add('bankBalance', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your bank balance']),
                    new Range(['min' => 1, 'minMessage' => 'Bank balance must be at least {{ limit }}'])
                ],
                'attr' => [
                    'style' => 'margin-bottom: 10px; padding: 5px;'
                ],
                'label_attr' => [
                    'style' => 'margin-right: 10px;'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
