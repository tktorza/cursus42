<?php

namespace Clab\ApiBundle\Form\Type\Log;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestSessionCaisseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateStart', 'text', array('required' => false, 'mapped' => false))
            ->add('dateEnd', 'text', array('required' => false, 'mapped' => false))
            ->add('cashFlowStart', null, array('required' => false))
            ->add('cashFlowDiff', null, array('required' => false))
            ->add('cashFlowEnd', null, array('required' => false))
            ->add('cashFlowEndTheoric', null, array('required' => false))
            ->add('refund', 'text', array('required' => false, 'mapped' => false))
            ->add('cash', null, array('required' => false))
            ->add('cb', null, array('required' => false))
            ->add('restoTicket', null, array('required' => false))
            ->add('check', null, array('required' => false))
            ->add('amex', null, array('required' => false))
            ->add('productSwitch', null, array('required' => false))
            ->add('commercialGesture', null, array('required' => false))
            ->add('accidentalDebit', null, array('required' => false))
            ->add('testError', null, array('required' => false))
            ->add('inOut', 'text', array('required' => false, 'mapped' => false))
            ->add('commentary', null, array('required' => false))
            ->add('device',null,array('required' => true))
            ->add('deviceName',null,array('required' => false))
            ->add('orders', null, array('required' => false, 'mapped' => false))
            ->add('vat', null, array('required' => false, 'mapped' => false))
            ->add('restaurantID', null, array('required' => true, 'mapped' => false ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\ApiBundle\Entity\SessionCaisse',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
