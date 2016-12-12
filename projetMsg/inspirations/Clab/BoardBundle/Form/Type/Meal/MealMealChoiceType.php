<?php

namespace Clab\BoardBundle\Form\Type\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MealMealChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_online', null, array('required' => false, 'label' => 'Disponible'))
            ->add('price', 'text', array('label' => 'pro.catalog.meal.slot.choice.priceLabel'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\MealChoice',
        ));
    }

    public function getName()
    {
        return 'board_meal_meal_choice';
    }
}
