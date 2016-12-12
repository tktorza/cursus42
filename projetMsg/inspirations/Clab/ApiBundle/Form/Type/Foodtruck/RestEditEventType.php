<?php

namespace Clab\ApiBundle\Form\Type\Foodtruck;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class RestEditEventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'integer', array('constraints' => array(new NotBlank())))
            ->add('start', 'integer', array('constraints' => array(new NotBlank())))
            ->add('new_start', 'integer', array('constraints' => array(new NotBlank())))
            ->add('new_end', 'integer', array('constraints' => array(new NotBlank())))
            ->add('name', null, array('constraints' => array(new NotBlank())))
            ->add('street', null, array('constraints' => array(new NotBlank())))
            ->add('zip', null, array('constraints' => array(new NotBlank())))
            ->add('city', null, array('constraints' => array(new NotBlank())))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
