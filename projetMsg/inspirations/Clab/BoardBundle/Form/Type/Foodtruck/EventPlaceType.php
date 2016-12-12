<?php

namespace Clab\BoardBundle\Form\Type\Foodtruck;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventPlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('place', 'entity', array(
                'class' => 'Clab\LocationBundle\Entity\Place',
                'label' => 'Choisir un lieu',
                'empty_value' => 'Aucun de ces lieux',
                'choices' => $options['places'],
                'required' => false,
                'expanded' => true,
                'multiple' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\TimeSheet',
            'places' => array(),
        ));
    }

    public function getName()
    {
        return 'board_foodtruck_event_place';
    }
}
