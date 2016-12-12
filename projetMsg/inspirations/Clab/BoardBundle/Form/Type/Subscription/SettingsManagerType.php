<?php

namespace Clab\BoardBundle\Form\Type\Subscription;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;

use libphonenumber\PhoneNumberFormat;

class SettingsManagerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('managerFirstName', null, array('label' => 'Prénom', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('managerName', null, array('label' => 'Nom', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('managerEmail', 'email', array('label' => 'Email', 'required' => true, 'constraints' => array(new Email())))
            ->add('managerPhone', 'tel', array('label' => 'Téléphone', 'required' => true, 'constraints' => array(new PhoneNumber()),
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_settings_manager';
    }
}
