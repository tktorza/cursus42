<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\DeliveryBundle\Form\Type\DeliveryScheduleType;

class DeliveryDayType extends AbstractType
{
    protected $deliveryMen = array();

    public function __construct(array $parameters)
    {
        if(isset($parameters['deliveryMen'])) {
            $this->deliveryMen = $parameters['deliveryMen'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', null, array(
                'label' => 'Heure de début',
                'required' => true,
                'widget' => 'single_text',
            ))
            ->add('end', null, array(
                'label' => 'Heure de fin',
                'required' => true,
                'widget' => 'single_text',
            ))
            ->add('deliveryMen', null, array(
                'label' => 'Livreur(s)',
                'choices' => $this->deliveryMen,
                'expanded' => true,
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\DeliveryDay'
        ));
    }

    public function getName()
    {
        return 'clab_delivery_day';
    }
}
