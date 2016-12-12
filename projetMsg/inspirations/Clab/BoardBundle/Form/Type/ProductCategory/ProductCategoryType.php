<?php

namespace Clab\BoardBundle\Form\Type\ProductCategory;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\RestaurantBundle\Entity\ProductCategory;

class ProductCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data'])) {
            $category = $options['data'];
        }
        if (!$category || ($category && $category instanceof ProductCategory && !$category->getParent())) {
            $builder
                ->add('name', null, array('label' => 'pro.name'))
                ->add('type', 'choice', array(
                    'label' => 'Type',
                    'choices' => array('Entrées' => 'Entrées', 'Plats' => 'Plats', 'Accompagnements' => 'Accompagnements', 'Desserts' => 'Desserts', 'Boissons' => 'Boissons', 'Autre' => 'Autre'),
                    'required' => false,
                    'empty_value' => 'Choisir un type',
                ))
                ->add('categoryGroup', 'choice', array(
                    'label' => 'Groupe',
                    'choices' => array("Matsuri à la carte" => "Matsuri à la carte", "Plateaux assortis" => "Plateaux assortis", "Spécialités et plats chauds" => "Spécialités et plats chauds", "Formules déjeuner" => "Formules déjeuner", "Boissons et accompagnements" => "Boissons et accompagnements"),
                    'required' => false,
                    'empty_value' => 'Choisir un groupe',
                ))
                ->add('description', null, array('label' => 'pro.description', 'required' => false))
            ;
        }

        $builder
            ->add('isOnline', 'checkbox', array('label' => 'pro.online', 'required' => false))
            ->add('products', 'collection', array(
                'type' => new ProductCategoryProductType(),
                'label' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\ProductCategory',
        ));
    }

    public function getName()
    {
        return 'board_product_category';
    }
}
