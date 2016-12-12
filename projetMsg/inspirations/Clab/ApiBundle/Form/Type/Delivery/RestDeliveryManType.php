<?php

namespace Clab\ApiBundle\Form\Type\Delivery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestDeliveryManType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', null, array('label' => 'Nom'))
            ->add('phone', null, array('label' => 'Téléphone'))
            ->add('isOnline', null,array('required' => false))
            ->add('code', null, array('required' => false))
            ->add('restaurantId', null, array('required' => false, 'mapped' => false))
            ->add('latitude', null, array('required' => false))
            ->add('longitude', null, array('required' => false))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\DeliveryMan',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'clab_rest_delivery_man';
    }
}
