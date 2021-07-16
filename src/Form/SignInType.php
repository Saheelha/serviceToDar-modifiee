<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('role',ChoiceType::class,[
                'choices'=>[
                    'Admin'=>'ROLE_ADMIN',
                    'Client'=>'ROLE_CLIENT',
                    'Fournisseur'=>'ROLE_FOURNISSEUR'
                    
                ]
            ])
            ->add('password',PasswordType::class)
            ->add('nom')
            ->add('prenom')
            ->add('tel')
            ->add('email',EmailType::class)
            ->add('adresseResidence')
            ->add('matriculeFiscale')
            ->add('adresseLocale')
            ->add('activationToken')
            ->add('username')
            ->add('categorieMetier')
            ->add('confirmPassword',PasswordType::class)
            ->add('pays')
            ->add('vote')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
