<?php

namespace Clab\BoardBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocialPostMealType extends AbstractType
{
    protected $meals = array();

    public function __construct($meals)
    {
        $this->meals = $meals;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('meal', 'entity', array(
            'class' => 'ClabRestaurantBundle:Meal',
            'data_class' => 'Clab\RestaurantBundle\Entity\Meal',
            'label' => 'pro.communication.post.mealLabel',
            'required' => true,
            'choices' => $this->meals,
            'expanded' => true,
            'data' => isset($this->meals[0]) ? $this->meals[0] : null,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\SocialBundle\Entity\SocialPost',
        ));
    }

    public function getName()
    {
        return 'board_social_post_meal';
    }
}
