<?php

namespace Clab\BoardBundle\Form\Type\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\Email;
use libphonenumber\PhoneNumberFormat;

use Clab\LocationBundle\Form\Type\AddressType;

class ClientRestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', new AddressType(), array(
                'required' => false,
                'label' => 'Adresse',
            ))
            ->add('phone', 'tel', array('label' => 'Téléphone', 'required' => false,
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL))
            ->add('email', null, array('label' => 'Email', 'required' => false))

            ->add('managerFirstName', null, array('label' => 'Prénom du manager', 'required' => false))
            ->add('managerName', null, array('label' => 'Nom du manager', 'required' => false))
            ->add('managerEmail', null, array('label' => 'Email du manager', 'required' => false))
            ->add('managerPhone', 'tel', array('label' => 'Téléphone', 'required' => false,
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
        return 'client_restaurant';
    }
}
