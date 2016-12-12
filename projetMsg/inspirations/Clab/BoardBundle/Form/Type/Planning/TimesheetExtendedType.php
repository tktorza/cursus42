<?php

namespace Clab\BoardBundle\Form\Type\Planning;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\RestaurantBundle\Entity\TimeSheet;
use Clab\BoardBundle\Form\Type\Location\AddressType;

class TimesheetExtendedType extends AbstractType
{
    protected $startDefault;
    protected $endDefault;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['startDefault'])) {
            $this->startDefault = date_create_from_format('G:i', $parameters['startDefault']);
        } else {
            $this->startDefault = date_create_from_format('G:i', '10:00');
        }

        if(isset($parameters['endDefault'])) {
            $this->endDefault = date_create_from_format('G:i', $parameters['endDefault']);
        } else {
            $this->endDefault = date_create_from_format('G:i', '20:00');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', new AddressType(true), array('required' => true, 'label' => 'pro.address.title'))
            ->add('isPrivate', null, array('required' => false, 'label' => 'pro.timesheets.eventType.private'))
            ->add('type', 'choice', array(
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => 'Type d\'évènement',
                'choices' => array(
                    Timesheet::TIMESHEET_TYPE_CLASSIC => 'Récurrent',
                    Timesheet::TIMESHEET_TYPE_EVENT => 'Ponctuel',
                ),
            ))
            ->add('start', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de début',
                'data' => isset($options['data']) ? $options['data']->getStart() : $this->startDefault
            ))
            ->add('end', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de fin',
                'data' => isset($options['data']) ? $options['data']->getEnd() : $this->endDefault
            ))
            ->add('startDate', 'date', array('required' => false, 'label' => 'pro.timesheets.labels.startDate',
                'widget' => 'single_text', 'format' =>  'dd/MM/yyyy'))
            ->add('endDate', null, array('required' => false, 'label' => 'pro.timesheets.labels.endDate',
                    'widget' => 'single_text', 'format' =>  'dd/MM/yyyy'))
            ->add('monday', null, array('label' => 'pro.days.monday', 'required' => false))
            ->add('tuesday', null, array('label' => 'pro.days.tuesday', 'required' => false))
            ->add('wednesday', null, array('label' => 'pro.days.wednesday', 'required' => false))
            ->add('thursday', null, array('label' => 'pro.days.thursday', 'required' => false))
            ->add('friday', null, array('label' => 'pro.days.friday', 'required' => false))
            ->add('saturday', null, array('label' => 'pro.days.saturday', 'required' => false))
            ->add('sunday', null, array('label' => 'pro.days.sunday', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\TimeSheet',
        ));
    }

    public function getName()
    {
        return 'board_restaurant_timesheet';
    }
}
