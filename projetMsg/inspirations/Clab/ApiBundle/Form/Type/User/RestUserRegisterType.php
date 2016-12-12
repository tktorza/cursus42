<?php

namespace Clab\ApiBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

use Clab\ApiBundle\Form\Type\User\RestUserRegisterProfileType;

class RestUserRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, array('constraints' => array(new Email(), new NotBlank())))
            ->add('password', null, array('constraints' => array(new NotBlank())))
            ->add('first_name', null, array('constraints' => array(new NotBlank())))
            ->add('last_name', null, array('constraints' => array(new NotBlank())))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false
        ));
    }

    public function getName()
    {
        return '';
    }
}
