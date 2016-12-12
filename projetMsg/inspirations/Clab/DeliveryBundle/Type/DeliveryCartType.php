<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeliveryCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', null, array('label' => 'Panier minimum (€)', 'required' => false))
            ->add('max', null, array('label' => 'Panier maximum (€)', 'required' => false))
            ->add('delay', null, array('label' => 'Temps de préparation (min)', 'required' => true))
            ->add('extra', null, array('label' => 'Supplément (€)', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\DeliveryCart'
        ));
    }

    public function getName()
    {
        return 'clab_delivery_cart';
    }
}
