<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class DeliveryManType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom'))
            ->add('phone', null, array('label' => 'Téléphone'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\DeliveryMan',
        ));
    }

    public function getName()
    {
        return 'clab_delivery_man';
    }
}
