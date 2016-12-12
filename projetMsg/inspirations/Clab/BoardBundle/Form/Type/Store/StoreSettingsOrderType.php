<?php

namespace Clab\BoardBundle\Form\Type\Store;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\ShopBundle\Repository\PaymentMethodRepository;

class StoreSettingsOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('averagePrice', null, array('label' => 'Prix moyen', 'required' => false))
            ->add('orderDelay', 'text', array('label' => 'pro.restaurant.clickeat.orderDelayLabel', 'required' => true))
            ->add('maxOrderBySlot', null, array('label' => 'pro.restaurant.clickeat.maxOrderBySlotLabel', 'required' => true))
            ->add('maxOrderPriceBySlot', null, array('label' => 'pro.restaurant.clickeat.maxOrderPriceBySlotLabel', 'required' => false))
            ->add('paymentMethods', 'entity', array(
                'label' => 'pro.restaurant.clickeat.paymentMethodsLabel',
                'required' => false, 'expanded' => true, 'multiple' => true,
                'class' => 'ClabShopBundle:PaymentMethod',
                'query_builder' => function (PaymentMethodRepository $er) {
                    return $er->createQueryBuilder('p');
                },
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_store_settings_order';
    }
}
