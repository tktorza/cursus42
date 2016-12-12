<?php

namespace Clab\BoardBundle\Form\Type\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
    protected $client = false;
    protected $stock = true;
    protected $name = true;

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['client']) && is_bool($parameters['client'])) {
            $this->client = $parameters['client'];
        }

        if (isset($parameters['stock']) && is_bool($parameters['stock'])) {
            $this->stock = $parameters['stock'];
        }

        if (isset($parameters['name']) && is_bool($parameters['name'])) {
            $this->name = $parameters['name'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->name) {
            $builder
                ->add('name', null, array('label' => 'pro.catalog.product.nameLabel', 'required' => true))
            ;
        }

        $builder
            ->add('isOnline', null, array('required' => false, 'label' => ' '))
            ->add('price', 'text', array('label' => 'pro.catalog.product.priceLabel'))
        ;

        if ($this->client) {
            $builder
                ->add('childrens', 'collection', array(
                    'type' => new ProductType(array('stock' => false, 'name' => false)),
                ))
            ;
        }

        if ($this->stock) {
            $builder
                ->add('stock', 'text', array('label' => 'Stock'))
                ->add('defaultStock', 'text', array('label' => 'pro.catalog.product.defaultStockLabel'))
                ->add('unlimitedStock', null, array('label' => ' ', 'required' => false))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Product',
        ));
    }

    public function getName()
    {
        return 'board_catalog_product';
    }
}
