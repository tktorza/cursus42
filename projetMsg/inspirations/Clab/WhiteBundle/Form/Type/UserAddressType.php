<?php

namespace Clab\WhiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required'=> true))
            ->add('street', null, array('label' => 'false', 'required' => true))
            ->add('zip', 'text', array(
                'label' => 'false',
                'required' => true,
                'pattern' => '[0-9]{5}'
            ))
            ->add('city', 'text', array('label' => 'false', 'required' => true))
            ->add('building', 'text', array('label' => 'false', 'required' => false))
            ->add('doorCode', 'text', array('label' => 'false', 'required' => false))
            ->add('staircase', 'text', array('label' => 'false', 'required' => false))
            ->add('floor','text', array('label' => 'false', 'required' => false))
            ->add('elevator', 'text', array('label' => 'false', 'required' => false))
            ->add('intercom', 'text', array('label' => 'false', 'required' => false))
            ->add('door', 'text', array('label' => 'false', 'required' => false))
            ->add('doorCode', 'text', array('label' => 'false', 'required' => false))
            ->add('secondDoorCode', 'text', array('label' => 'false', 'required' => false))
            ->add('comment', 'textarea', array('label' => 'false', 'required' => false))
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\LocationBundle\Entity\Address',
        ));
    }

    public function getName()
    {
        return 'white_user_address';
    }
}
