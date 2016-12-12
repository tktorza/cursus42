<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\DeliveryBundle\Form\Type\DeliveryDayEventType;

class DeliveryScheduleEventType extends AbstractType
{
    protected $deliveryMen = array();

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['deliveryMen']) && is_array($parameters['deliveryMen'])) {
            $this->deliveryMen = $parameters['deliveryMen'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom'))
            ->add('color', 'choice', array('label' => 'Couleur', 'required' => true, 'choices' => array(
                '1abc9c' => 'Turquoise',
                '3498db' => 'Bleu',
                '9b59b6' => 'Violet',
                'f1c40f' => 'Jaune',
                'e67e22' => 'Orange',
            )))
            ->add('distance', null, array('label' => 'Distance max (m)'))
            ->add('slotLength', 'choice', array(
                'label' => 'Taille du créneau',
                'choices' => array(15 => '15 minutes', 20 => '20 minutes', 30 => '30 minutes'),
                'expanded' => true,
                'required' => true,
            ))
            ->add('deliveryDays', 'collection', array(
                'type' => new DeliveryDayEventType(array('deliveryMen' => $this->deliveryMen)),
                'options'  => array(
                    'required'  => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\DeliverySchedule'
        ));
    }

    public function getName()
    {
        return 'clab_delivery_schedule_event';
    }
}
