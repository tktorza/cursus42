<?php

namespace Clab\BoardBundle\Form\Type\Store;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Planning\TimesheetPreorderType;

class StorePreorderType extends AbstractType
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
        $builder->add('isOpen', null, array('label' => 'Activer la précommande', 'required' => false));

        if(is_array($this->weekDays) && count($this->weekDays) > 0) {

            $week = array(
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
                7 => 'Dimanche'
            );

            foreach ($week as $day => $label) {
                $builder
                    ->add('is_weekday_' . $day, 'checkbox', array(
                        'required' => false,
                        'label' => $label,
                        'mapped' => false,
                        'data' => (count($this->weekDays[$day]) > 0) ? true : false,
                    ))
                    ->add('timesheets_' . $day, 'collection', array(
                        'type' => new TimesheetPreorderType(),
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_store_preorder';
    }
}
