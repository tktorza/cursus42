<?php

namespace Clab\BoardBundle\Form\Type\Caisse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CaisseLabelType extends AbstractType
{

    public function __construct(array $parameters = array())
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', 'text', array('required' => false, 'attr' => array('class' => 'form-control col-xs-12')))
        ;
    }

    public function getName()
    {
        return 'board_caisse_settings_label';
    }
}
