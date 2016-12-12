<?php

namespace Clab\WhiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_male', 'choice', array(
                'required' => true,
                'choices' => array(true => 'Mr.', false => 'Mme.'),
            ))
            ->add('phone', null, array('required' => true))
            ->add('first_name', null, array('required' => true))
            ->add('last_name', null, array('required' => true))
            ->add('email', 'email', array('label' => 'clickeat.form.label.email', 'constraints' => array(new Email(), new NotBlank()), 'required' => true))
            ->add('plainPassword', 'password')
            ->add('verifyPlainPassword', 'password', array('mapped' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'clab_white_register';
    }
}
