<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/23/17
 * Time: 8:52 PM
 */

namespace App\Form\Security;


use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class ResetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('_username',HiddenType::class)
                ->add('_token', HiddenType::class)
                ->add('_password',RepeatedType::class,
                            ['type'=> PasswordType::class]);
    }

}