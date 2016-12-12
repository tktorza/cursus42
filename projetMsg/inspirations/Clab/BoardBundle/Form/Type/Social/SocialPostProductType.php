<?php

namespace Clab\BoardBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocialPostProductType extends AbstractType
{
    protected $products = array();

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('product', 'entity', array(
            'class' => 'ClabRestaurantBundle:Product',
            'data_class' => 'Clab\RestaurantBundle\Entity\Product',
            'label' => 'pro.communication.post.productLabel',
            'required' => true,
            'choices' => $this->products,
            'expanded' => true,
            'data' => isset($this->products[0]) ? $this->products[0] : null,
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
        return 'board_social_post_product';
    }
}
