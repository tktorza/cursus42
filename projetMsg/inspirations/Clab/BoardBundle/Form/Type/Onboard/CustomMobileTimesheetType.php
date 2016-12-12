<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Clab\LocationBundle\Form\Type\AddressType;

class CustomMobileTimesheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('days', 'choice', array(
                'mapped' => false,
                'label' => 'Jour(s)',
                'choices' => array(1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'),
                'multiple' => true,
            ))
            ->add('start', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de début',
                'constraints' => array(new NotBlank()),
            ))
            ->add('end', null, array(
                'required' => true,
                'widget' => 'single_text',
                'label' => 'Heure de fin',
                'constraints' => array(new NotBlank()),
            ))
            ->add('address', new AddressType(array('name' => true)), array(
                'required' => true,
                'label' => 'Adresse'
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
