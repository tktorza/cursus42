<?php

namespace Clab\LocationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    protected $name = false;
    protected $delivery = false;

    public function __construct($name = false, $delivery=false) {
        $this->name = $name;
        $this->delivery = $delivery;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->name) {
            $builder->add('name', null, array('label' => 'pro.address.nameLabel', 'required' => false));
        }

        $builder
            ->add('street', null, array('label' => 'pro.address.streetLabel', 'required' => true))
            ->add('zip', 'text', array(
                'label' => 'pro.address.zipLabel',
                'required' => true,
                'pattern' => '[0-9]{5}'
            ))
            ->add('city', null, array('label' => 'pro.address.cityLabel', 'required' => true))
        ;

        if($this->delivery) {
            $builder
                ->add('intercom',null,array('label' => 'digicode', 'required' => false))
                ->add('floor',null,array('label' => 'étage', 'required' => false))
                ->add('door',null,array('label' => 'porte', 'required' => false))
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\LocationBundle\Entity\Address',
        ));
    }

    public function getName()
    {
        return 'location_address';
    }
}
