<?php

namespace Clab\BoardBundle\Form\Type\Store;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\LocationBundle\Form\Type\AddressType;
use Clab\BoardBundle\Form\Type\Store\StoreProfileSocialType;

use Symfony\Component\Validator\Constraints\Email;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;

use libphonenumber\PhoneNumberFormat;

class RestaurantType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('address', new AddressType(), array(
                  'required' => false,
                  'label' => 'pro.address.title',
              ))
            ->add('emailPayment', null, array('label' => 'Email de paiement (Stripe)', 'required' => false))
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
        return 'board_store_profile';
    }
}
