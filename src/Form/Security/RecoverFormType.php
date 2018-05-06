<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/23/17
 * Time: 1:39 PM
 */

namespace App\Form\Security;

use App\Entity\Access\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\Email;

class RecoverFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_email', EmailType::class);
    }

/*    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>User::class,
        ]);
    }*/
}