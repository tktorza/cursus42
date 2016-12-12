<?php

namespace Clab\LocationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class EventScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startTime', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de début',
                'constraints' => array(new NotBlank())
            ))
            ->add('endTime', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de fin',
                'constraints' => array(new NotBlank())
            ))
            ->add('startDate', 'date', array(
                'required' => false,
                'label' => 'pro.timesheets.labels.startDate',
                'widget' => 'single_text',
                'format' =>  'dd/MM/yyyy',
                'constraints' => array(new NotBlank())
            ))
            ->add('endDate', null, array(
                'required' => false,
                'label' => 'pro.timesheets.labels.endDate',
                'widget' => 'single_text',
                'format' =>  'dd/MM/yyyy',
                'constraints' => array(new NotBlank())
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\LocationBundle\Entity\EventSchedule',
        ));
    }

    public function getName()
    {
        return 'location_event_schedule';
    }
}
