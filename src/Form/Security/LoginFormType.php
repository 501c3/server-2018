<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/13/17
 * Time: 8:48 PM
 */

namespace App\Form\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginFormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
     $builder
         ->add('_username')
         ->add('_password', PasswordType::class);
  }
}