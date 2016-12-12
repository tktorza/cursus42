<?php

namespace Clab\BoardBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

use Clab\BoardBundle\Form\Type\User\RegisterProfileType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'clickeat.form.label.email', 'constraints' => array(new Email(), new NotBlank())))
            ->add('plainPassword', 'password', array('label' => 'Votre mot de passe'))
            ->add('first_name', null, array('label' => 'clickeat.form.label.firstname', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('last_name', null, array('label' => 'clickeat.form.label.lastname', 'required' => true, 'constraints' => array(new NotBlank())))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'board_register';
    }
}
