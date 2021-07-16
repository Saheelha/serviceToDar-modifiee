<?php

namespace App\Form;

use DateTime;
use DateInterval;
use App\Entity\Produit;
use Symfony\Component\Form\Extension\Core\Type\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AjouterProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prix')
            ->add('description')
            ->add('quantite')
            ->add('lienVideo',TextType::class,[
                'required'=>false,
                'mapped'=>true
            ])
            ->add('images',FileType::class,[
                'required'=>false,
                'mapped'=>false,
                'multiple'=>true
            ])
            ->add('dateLivraison',ChoiceType::class,[
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
            ->add('prixLivraison')
            ->add('poids')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
    public function creationDeDate( $a){
        $date=new DateTime('now');
        $t='P'.$a.'D';
        $interval = new DateInterval($t);
        $date->add($interval);
        return $date;
    }
}
