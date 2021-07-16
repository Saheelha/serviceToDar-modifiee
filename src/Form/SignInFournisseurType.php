<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SignInFournisseurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('categorieMetier')
            ->add('gouvernorat', ChoiceType::class, [
                'choices' => [
                    'Ariana' => 'Ariana',
                    'Béja' => 'Béja',
                    'Ben Arous' => 'Ben Arous',
                    'Gabes' => 'Gabes',
                    'Bizerte' => 'Bizerte',
                    'Gafsa' => 'Gafsa',
                    'Jendouba' => 'Jendouba',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Kebli' => 'Kebli',
                    'La Manouba' => 'La Manouba',
                    'Le Kef' => 'Le Kef',
                    'Mahdia' => 'Mahdia',
                    'Médenine' => 'Médenine',
                    'Monastir' => 'Monastir',
                    'Nabeul' => 'Nabeul',
                    'Sfax' => 'Sfax',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Siliana' => 'Siliana',
                    'Sousse' => 'Sousse',
                    'Tataouine' => 'Tataouine',
                    'Tozeur' => 'Tozeur',
                    'Tunis' => 'Tunis',
                    'Zaghouan' => 'Zaghouan'
                ]
            ])
            ->add('tel', TelType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '8'
                ]
            ])
            ->add('photo', FileType::class, [
                'mapped' => false
            ])
            ->add('matriculeFiscale', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => '255'
                ]
            ])
            ->add('adresse',TextType::class,[
                'required'=>true,
                'attr'=>[
                    'maxlength'=>'255'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
