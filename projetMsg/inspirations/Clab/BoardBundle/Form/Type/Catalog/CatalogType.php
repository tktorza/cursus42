<?php

namespace Clab\BoardBundle\Form\Type\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Catalog\ProductType;
use Clab\BoardBundle\Form\Type\Catalog\ProductCategoryType;

class CatalogType extends AbstractType
{
    protected $categories = array();
    protected $client = false;
    protected $meals = array();

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['categories']) && is_array($parameters['categories'])) {
            $this->categories = $parameters['categories'];
        }

        if (isset($parameters['meals']) && is_array($parameters['meals'])) {
            $this->meals = $parameters['meals'];
        }

        if (isset($parameters['client']) && is_bool($parameters['client'])) {
            $this->client = $parameters['client'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categories', 'collection', array(
                'type' => new ProductCategoryType(array('client' => $this->client)),
                'data' => $this->categories
            ))
        ;

        if (count($this->meals)>0) {
            $builder
                ->add('meals','collection', array(
                    'type' => new MealType(array('client' => $this->client)),
                    'data' => $this->meals
                ));
        }
    }

    public function getName()
    {
        return 'board_catalog';
    }
}
