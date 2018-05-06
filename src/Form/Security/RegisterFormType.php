<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/22/17
 * Time: 5:01 PM
 */

namespace App\Form\Security;


use App\Entity\Access\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', ChoiceType::class,
                        ['choices'=>[""=>"",
                                     'Mr'=>'Mr',
                                     'Mrs'=>'Mrs',
                                     'Ms'=>'Ms',
                                     'Dr'=>'Dr']])
            ->add('first',  TextType::class)
            ->add('middle', TextType::class)
            ->add('last', TextType::class)
            ->add('suffix', TextType::class)
            ->add('mobile', TelType::class)
            ->add('username', TextType::class)
            ->add('email', RepeatedType::class,
                ['type'=>EmailType::class])
            ->add('plainPassword', RepeatedType::class,
                ['type'=>PasswordType::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>User::class,
            'validation_groups'=> ['Default', 'Register']

        ]);
    }
}