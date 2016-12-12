<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaDeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('color', null, array('label' => 'Couleur', 'required' => true))
            ->add('isOnline','checkbox', array('label' => 'En ligne','required' => false))
            ->add('zone',null, array('label' => 'Zone', 'required' => true))
            ->add('price',null, array('label' => 'Coût de la livraison'))
            ->add('minPanier',null, array('label' => 'Minimum panier'))
            ->add('slotLength', 'choice', array('label' => 'Temps de livraison', 'required' => true,
                'choices' => array(
                    10=>'10 minutes',
                    20=>'20 minutes',
                    30=>'30 minutes',
                    40=>'40 minutes',
                    50=>'50 minutes',
                    60=>'1 heures',
                    70=>'1 heures et 10 minutes',
                    80=>'1 heures et 20 minutes',
                    90=>'1 heures et 30 minutes',
                ),
                'expanded' => false,
            ))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\AreaDelivery',
        ));
    }

    public function getName()
    {
        return 'clab_area_delivery';
    }
}
