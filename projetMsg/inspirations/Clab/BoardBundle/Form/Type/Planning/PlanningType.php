<?php

namespace Clab\BoardBundle\Form\Type\Planning;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Planning\TimesheetType;

class PlanningType extends AbstractType
{
    protected $weekDays = array();
    protected $upcomingEvent = array();

    public function __construct(array $parameters)
    {
        if (isset($parameters['weekDays']) && is_array($parameters['weekDays'])) {
            $this->weekDays = $parameters['weekDays'];
        }

        if (isset($parameters['upcomingEvent']) && is_array($parameters['upcomingEvent'])) {
            $this->upcomingEvent = $parameters['upcomingEvent'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('isOpen', null, array('label' => 'Activer la commande à emporter', 'required' => false));

        if (is_array($this->weekDays) && count($this->weekDays) > 0) {
            $week = array(
                'MONDAY' => 'Lundi',
                'TUESDAY' => 'Mardi',
                'WEDNESDAY' => 'Mercredi',
                'THURSDAY' => 'Jeudi',
                'FRIDAY' => 'Vendredi',
                'SATURDAY' => 'Samedi',
                'SUNDAY' => 'Dimanche'
            );

            foreach ($week as $day => $label) {
                $builder
                    ->add('is_weekday_' . $day, 'checkbox', array(
                        'required' => false,
                        'label' => $label,
                        'mapped' => false,
                        'data' => isset($this->weekDays[$day]) && (count($this->weekDays[$day]) > 0) ? true : false,
                    ))
                    ->add('timesheets_' . $day, 'collection', array(
                        'type' => new TimesheetType(),
                        'options'  => array(
                            'required'  => false,
                        ),
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'mapped' => false,
                        'data' => $this->weekDays[$day]
                    ))
                ;
            }
        }

        if (is_array($this->upcomingEvent) && count($this->upcomingEvent) > 0) {
            $builder
                ->add('upcomingEvent', 'collection', array(
                    'type' => new TimesheetType(array('startDefault' => '00:00', 'endDefault' => '23:59')),
                    'options'  => array(
                        'required'  => false,
                    ),
                    'data' => $this->upcomingEvent,
                    'mapped' => false,
                ))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_store_planning';
    }
}
