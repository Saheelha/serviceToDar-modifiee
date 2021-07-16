<?php

namespace App\Form;

use DateTime;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AjouterServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prix')
            ->add('description')
            ->add('images',FileType::class,[
                'required'=>false,
                'mapped'=>false,
                'multiple'=>true
            ])
            ->add('lienVideo',TextType::class,[
                'required'=>false,
                'mapped'=>true
            ])
            ->add('dateReservation',ChoiceType::class,[
                'choices'=>[
                    '24h'=>'1',
                    '48h'=>'2',
                    '3j'=>'3',
                    '4j'=>'4',
                    '5j'=>'5',
                    '6j'=>'6',
                    '7j'=>'7'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
