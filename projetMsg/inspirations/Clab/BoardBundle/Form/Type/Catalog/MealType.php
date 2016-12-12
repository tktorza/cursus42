<?php

namespace Clab\BoardBundle\Form\Type\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MealType extends AbstractType
{
    protected $client = false;
    protected $name = true;

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['client']) && is_bool($parameters['client'])) {
            $this->client = $parameters['client'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->client) {
            $builder
                ->add('name', null, array('label' => 'pro.catalog.meal.nameLabel', 'required' => true))
            ;
        }

        $builder
            ->add('isOnline', null, array('required' => false, 'label' => ' '))
            ->add('price', 'text', array('label' => 'pro.catalog.meal.priceLabel'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Meal',
        ));
    }

    public function getName()
    {
        return 'board_catalog_meal';
    }
}
