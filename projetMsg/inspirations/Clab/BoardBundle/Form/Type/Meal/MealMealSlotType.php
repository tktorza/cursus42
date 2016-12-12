<?php

namespace Clab\BoardBundle\Form\Type\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Meal\MealMealChoiceType;

class MealMealSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('choices', 'collection', array(
                'type' => new MealMealChoiceType(),
            ))*/
            ->add('position', 'hidden')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\MealSlot',
        ));
    }

    public function getName()
    {
        return 'board_meal_meal_slot';
    }
}
