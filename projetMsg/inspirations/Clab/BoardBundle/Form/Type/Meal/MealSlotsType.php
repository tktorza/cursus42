<?php

namespace Clab\BoardBundle\Form\Type\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Meal\MealSlotType;

class MealSlotsType extends AbstractType
{
    protected $slots = array();

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['slots']) && is_array($parameters['slots'])) {
            $this->slots = $parameters['slots'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(count($this->slots) > 0) {
            $builder->add('new_slots', 'entity', array(
                'class' => 'ClabRestaurantBundle:MealSlot',
                'label' => 'Ajouter des étapes',

                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->slots,
                'mapped' => false,
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Meal',
        ));
    }

    public function getName()
    {
        return 'board_meal_slots';
    }
}
