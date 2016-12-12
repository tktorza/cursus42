<?php

namespace Clab\BoardBundle\Form\Type\Caisse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CaisseSettingsType extends AbstractType
{
    private $products = null;

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['products'])) {
            $this->products = $parameters['products'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('caisseDiscountsLabels', 'collection', array(
                'type' => new CaisseLabelType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => " "
            ))
            ->add('caisseTags', 'collection', array(
                'type'   => new CaisseLabelType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => " "
            ))
            ->add('caissePrinterLabels', 'collection', array(
                'type'   => new CaissePrinterLabelType($this->products),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => " "
            ))
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
        return 'board_caisse_settings';
    }
}
