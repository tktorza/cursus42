<?php

namespace Clab\BoardBundle\Form\Type\Foodtruck;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', 'entity', array(
                'class' => 'Clab\LocationBundle\Entity\Event',
                'label' => 'Choisir un évènement',
                'empty_value' => 'Aucun de ces évènements',
                'choices' => $options['events'],
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
            'events' => array(),
        ));
    }

    public function getName()
    {
        return 'board_foodtruck_event_event';
    }
}
