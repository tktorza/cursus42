<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Onboard\CustomMobileTimesheetType;

class CustomMobilePlanningType extends AbstractType
{
    protected $timesheets = array();

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['timesheets']) && is_array($parameters['timesheets'])) {
            $this->timesheets = $parameters['timesheets'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timesheets', 'collection', array(
                'type' => new CustomMobileTimesheetType(),
                'options'  => array(
                    'required'  => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'data' => $this->timesheets,
            ))
        ;
    }

    public function getName()
    {
        return 'board_onboard_custom_planning';
    }
}