<?php

namespace Clab\BoardBundle\Form\Type\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Catalog\ProductType;

class ProductCategoryType extends AbstractType
{
    protected $client = false;

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['client']) && is_bool($parameters['client'])) {
            $this->client = $parameters['client'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('products', 'collection', array(
                'type' => new ProductType(array('client' => $this->client, 'stock' => !$this->client)),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\ProductCategory',
        ));
    }

    public function getName()
    {
        return 'board_catalog_product_category';
    }
}
