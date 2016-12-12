<?php

namespace Clab\BoardBundle\Form\Type\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\BoardBundle\Form\Type\Meal\MealSlotCategoryType;

class MealSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'label' => 'pro.catalog.option.nameLabel',
            ))
        ;

        if (isset($options['data']) && $options['data'] instanceof MealSlot) {
            $slot = $options['data'];
            foreach ($slot->getProductCategories() as $productCategory) {
                foreach ($productCategory->getProducts() as $product) {
                    $builder
                        ->add('product_' . $product->getId() . '_disabled', 'checkbox', array(
                            'mapped' => false,
                            'required' => false,
                            'data' => !in_array($product->getId(), $slot->getDisabledProducts())
                        ))
                        ->add('product_' . $product->getId() . '_price', 'text', array(
                            'mapped' => false,
                            'required' => false,
                            'data' => isset($slot->getCustomPrices()[$product->getId()]) ? $slot->getCustomPrices()[$product->getId()] : null,
                        ))
                    ;
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\MealSlot',
        ));
    }

    public function getName()
    {
        return 'board_meal_slot';
    }
}
