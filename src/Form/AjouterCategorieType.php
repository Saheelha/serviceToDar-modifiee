<?php

namespace App\Form;

use App\Entity\CategorieMetier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjouterCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => 'true',
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('icone', FileType::class, [
                'mapped' => true,
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'required' => 'true',
                'attr' => [
                    'maxlength' => '500'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CategorieMetier::class,
        ]);
    }
}
