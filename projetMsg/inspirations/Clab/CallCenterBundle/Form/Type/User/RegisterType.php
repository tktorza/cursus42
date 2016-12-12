<?php

namespace Clab\CallCenterBundle\Form\Type\User;

use Clab\LocationBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', null, array('required' => true))
            ->add('last_name', null, array('required' => true))
            ->add('birthday', 'birthday', array(
                    'label' => 'Date de naissance',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'choice',
                    'years' => range(date('Y') -13, date('Y') -100),
                    'placeholder' =>array(
                        'years' => 'Année','months' => 'Mois','days' => 'Jour'
                    )
                )
            )
            ->add('homeAddress',new AddressType(false, false, false))
            ->add('is_male', 'choice', array(
                'choices'  => array(
                    'Homme' => true,
                    'Femme' => false,
                ),
                'choices_as_values' => true,
                'label' => 'Sexe',
                'required' => true
            ))
            ->add('email', 'email', array('label' => 'clickeat.form.label.email', 'constraints' => array(new Email()), 'required' => false, 'mapped' => false))
            ->add('plainPassword', 'password', array('required' => false, 'mapped' => false))
            ->add('phone', 'text', array(
                'label' => 'Téléphone',
                'required' => true
            ))
            ->add('company',null, array('label' => 'Entreprise', 'required' => false))
            ->add('business',null, array('label' => 'Société', 'required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'clab_call_center_register';
    }
}
