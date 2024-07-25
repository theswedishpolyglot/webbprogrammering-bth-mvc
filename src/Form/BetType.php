<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        unset($options);

        $builder
            ->add('hands', IntegerType::class, [
                'label' => 'Number of Hands',
                'attr' => [
                    'min' => 1,
                    'max' => 3,
                    'style' => 'margin-bottom: 10px; padding: 5px;'
                ],
                'label_attr' => [
                    'style' => 'margin-right: 10px;'
                ],
                'data' => 1
            ])
            ->add('bet', IntegerType::class, [
                'label' => 'Bet Amount',
                'attr' => [
                    'min' => 1,
                    'style' => 'margin-bottom: 10px; padding: 5px;'
                ],
                'label_attr' => [
                    'style' => 'margin-right: 10px;'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Start Game',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'style' => 'margin-top: 10px;'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
