<?php

namespace Clab\MultisiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

use libphonenumber\PhoneNumberFormat;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Votre nom et prénom', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('email', 'email', array('label' => 'Email', 'required' => true, 'constraints' => array(new NotBlank(), new Email())))
            ->add('phone', 'tel', array('label' => 'Téléphone', 'required' => false,
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL))
            ->add('message', 'textarea', array('label' => 'Message', 'required' => true))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        
    }

    public function getName()
    {
        return 'multisite_contact';
    }
}
