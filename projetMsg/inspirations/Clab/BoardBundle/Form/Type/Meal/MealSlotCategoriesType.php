<?php

namespace Clab\BoardBundle\Form\Type\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MealSlotCategoriesType extends AbstractType
{
    protected $categories = array();

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['categories']) && is_array($parameters['categories'])) {
            $this->categories = $parameters['categories'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('productCategories', null, array(
            'label' => 'Catégories',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->categories,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\MealSlot',
        ));
    }

    public function getName()
    {
        return 'board_meal_slot_categories';
    }
}
