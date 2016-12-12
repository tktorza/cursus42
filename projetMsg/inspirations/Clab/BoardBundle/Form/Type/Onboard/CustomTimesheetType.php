<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class CustomTimesheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', null, array(
                'required' => true,
                'widget' => 'single_text',
                'constraints' => array(new NotBlank()),
            ))
            ->add('end', null, array(
                'required' => true,
                'widget' => 'single_text',
                'constraints' => array(new NotBlank()),
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
        return 'board_onboard_custom_timesheet';
    }
}
