<?php

namespace Clab\BoardBundle\Form\Type\Foodtruck;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\RestaurantBundle\Entity\TimeSheet;

class EventScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => false,
                'choices' => array(
                    Timesheet::TIMESHEET_TYPE_CLASSIC => 'Récurrent',
                    Timesheet::TIMESHEET_TYPE_EVENT => 'Ponctuel',
                ),
            ))
            ->add('isPrivate', null, array('required' => false, 'label' => 'pro.timesheets.eventType.private'))
            ->add('start', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de début',
            ))
            ->add('end', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de fin',
            ))
            ->add('startDate', 'date', array('required' => false, 'label' => 'pro.timesheets.labels.startDate',
                'widget' => 'single_text', 'format' =>  'dd/MM/yyyy'))
            ->add('endDate', null, array('required' => false, 'label' => 'pro.timesheets.labels.endDate',
                    'widget' => 'single_text', 'format' =>  'dd/MM/yyyy'))
            ->add('days', 'choice', array(
                'label' => 'Jours',
                'choices' => array(
                    'MONDAY' => 'Lundi',
                    'TUESDAY' => 'Mardi',
                    'WEDNESDAY' => 'Mercredi',
                    'THURSDAY' => 'Jeudi',
                    'FRIDAY' => 'Vendredi',
                    'SATURDAY' => 'Samedi',
                    'SUNDAY' => 'Dimanche'
                ),
                'mapped' => false,
                'expanded' => true,
                'multiple' => true,
                'data' => $options['data']->getDays(),
            ))
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
        return 'board_foodtruck_event_address';
    }
}
