<?php

namespace Clab\ApiOldBundle\Form\Type\ProductCategory;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\ApiOldBundle\Form\DataTransformer\BooleanFieldTransformer;

class RestProductCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanFieldTransformer();

        $builder
            ->add('name')
            ->add($builder->create('is_online', 'text')->addModelTransformer($transformer))
            ->add('description')
            ->add('cover', 'file', array('mapped' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\ProductCategory',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
