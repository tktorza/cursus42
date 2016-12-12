<?php

namespace Clab\BoardBundle\Form\Type\Planning;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class TimesheetPreorderType extends AbstractType
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
        return 'board_restaurant_timesheet_preorder';
    }
}
