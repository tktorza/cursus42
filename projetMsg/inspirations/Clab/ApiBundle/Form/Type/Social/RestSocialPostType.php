<?php

namespace Clab\ApiBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RestSocialPostType extends AbstractType
{
    protected $products = array();
    protected $meals = array();
    protected $discounts = array();

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['products'])) {
            $this->products = $parameters['products'];
        }

        if(isset($parameters['meals'])) {
            $this->meals = $parameters['meals'];
        }

        if(isset($parameters['discounts'])) {
            $this->discounts = $parameters['discounts'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('message')
            ->add('image', 'file')
            ->add('to_facebook', 'checkbox', array('mapped' => false))
            ->add('to_twitter', 'checkbox', array('mapped' => false))
            ->add('add_link', 'checkbox', array('mapped' => false))
            ->add('product', 'entity', array(
                'class' => 'Clab\RestaurantBundle\Entity\Product',
                'choices' => $this->products
            ))
            ->add('meal', 'entity', array(
                'class' => 'Clab\RestaurantBundle\Entity\Meal',
                'choices' => $this->meals
            ))
            ->add('discount', 'entity', array(
                'class' => 'Clab\ShopBundle\Entity\Discount',
                'choices' => $this->discounts
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\SocialBundle\Entity\SocialPost',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
