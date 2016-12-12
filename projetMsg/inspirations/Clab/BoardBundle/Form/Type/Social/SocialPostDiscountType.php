<?php

namespace Clab\BoardBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocialPostDiscountType extends AbstractType
{
    protected $discounts = array();

    public function __construct($discounts)
    {
        $this->discounts = $discounts;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('discount', 'entity', array(
            'class' => 'ClabShopBundle:Discount',
            'data_class' => 'Clab\ShopBundle\Entity\Discount',
            'label' => 'pro.communication.post.discountLabel',
            'required' => true,
            'choices' => $this->discounts,
            'expanded' => true,
            'data' => isset($this->discounts[0]) ? $this->discounts[0] : null,
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
        return 'board_social_post_discount';
    }
}
