<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Onboard\CustomTimesheetType;

class CustomPlanningType extends AbstractType
{
    protected $weekDays = array();

    public function __construct(array $parameters)
    {
        if(isset($parameters['weekDays']) && is_array($parameters['weekDays'])) {
            $this->weekDays = $parameters['weekDays'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(is_array($this->weekDays) && count($this->weekDays) > 0) {

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
                        'type' => new CustomTimesheetType(),
                        'options'  => array(
                            'required'  => false,
                        ),
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'mapped' => false,
                        'data' => $this->weekDays[$day],
                        'label' => false,
                    ))
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_onboard_custom_planning';
    }
}
