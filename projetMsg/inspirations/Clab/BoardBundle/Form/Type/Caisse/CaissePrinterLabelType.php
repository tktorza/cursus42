<?php

namespace Clab\BoardBundle\Form\Type\Caisse;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\VarDumper\VarDumper;


class CaissePrinterLabelType extends AbstractType
{
    private $products;

    public function __construct($products = null)
    {
        $this->products = $products;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('printerLabel', 'text', array('required' => false, 'label' => 'label imprimante', 'attr' => array('class' => 'form-control col-xs-3')))
            ->add('printerId', 'text', array('required' => false, 'label' => 'Id imprimante', 'attr' => array('class' => 'form-control col-xs-3')))
        ;
        if ($this->products) {
            $builder
                ->add('products',  'choice', array(
                    'choices' => $this->products,
                    'choices_as_values' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'label' => 'Produits',
                    'required' => false,
                    'attr' => array('class' => 'select2 col-xs-12 no-padding')
                ))
            ;
        }
    }

    public function getName()
    {
        return 'board_caisse_settings_label';
    }
}
