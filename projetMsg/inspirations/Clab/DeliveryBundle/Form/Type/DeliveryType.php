<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\LocationBundle\Form\Type\AddressType;

class DeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', null, array(
                'label' => 'Commentaire sur l\'adresse',
                'required' => false,
                'data' => isset($options['data']) && $options['data'] && method_exists($options['data'], 'getAddress') ? $options['data']->getAddress()->getComment() : null,
            ))
            /*->add('address', new AddressType(false), array(
                'required' => true,
                'label' => 'Adresse',
            ))*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\Delivery'
        ));
    }

    public function getName()
    {
        return 'clab_delivery_delivery';
    }
}
