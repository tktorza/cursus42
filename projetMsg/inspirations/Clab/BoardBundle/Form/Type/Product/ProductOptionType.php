<?php

namespace Clab\BoardBundle\Form\Type\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Product\ProductOptionChoiceType;

class ProductOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('multiple', 'choice', array(
                'label' => 'pro.catalog.option.multipleLabel',
                'required' => true,
                'choices' => array(
                    0 => 'Choix simple',
                    1 => 'Choix multiple'
                )
            ))
            ->add('required', 'choice', array(
                'label' => 'pro.catalog.option.requiredLabel',
                'required' => true,
                'choices' => array(
                    0 => 'Non requis',
                    1 => 'Requis'
                )
            ))
            ->add('minimum', null, array('label' => 'Minimum'))
            ->add('maximum', null, array('label' => 'Maximum'))
            ->add('choices', 'collection', array(
                'type' => new ProductOptionChoiceType(),
            ))*/
            ->add('position', 'hidden')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\ProductOption',
        ));
    }

    public function getName()
    {
        return 'board_product_option';
    }
}
