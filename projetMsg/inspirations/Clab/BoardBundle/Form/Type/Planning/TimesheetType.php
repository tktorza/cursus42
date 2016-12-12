<?php

namespace Clab\BoardBundle\Form\Type\Planning;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class TimesheetType extends AbstractType
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
            ->add('start', null, array(
                'required' => true,
                'widget' => 'single_text',
                'constraints' => array(new NotBlank()),
                //'data' => isset($options['data']) ? $options['data']->getStart() : $this->startDefault
            ))
            ->add('end', null, array(
                'required' => true,
                'widget' => 'single_text',
                'constraints' => array(new NotBlank()),
                //'data' => isset($options['data']) ? $options['data']->getEnd() : $this->endDefault
            ))
            ->add('maxPreorderTime', null, array(
                'required' => false,
                'widget' => 'single_text',
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
        return 'board_restaurant_timesheet';
    }
}
