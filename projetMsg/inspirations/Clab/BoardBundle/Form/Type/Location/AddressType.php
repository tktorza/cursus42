<?php

namespace Clab\BoardBundle\Form\Type\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    protected $name = false;

    public function __construct($name = false)
    {
        $this->name = $name;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->name) {
            $builder->add('name', null, array('label' => 'pro.address.nameLabel', 'required' => false));
        }

        $builder->add('street', null, array('label' => 'pro.address.streetLabel', 'required' => true));
        $builder->add('zip', null, array('label' => 'pro.address.zipLabel', 'required' => true));
        $builder->add('city', null, array('label' => 'pro.address.cityLabel', 'required' => true));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\LocationBundle\Entity\Address',
        ));
    }

    public function getName()
    {
        return 'board_location_address';
    }
}
