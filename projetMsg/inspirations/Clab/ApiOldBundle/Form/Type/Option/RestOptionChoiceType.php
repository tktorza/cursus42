<?php

namespace Clab\ApiOldBundle\Form\Type\Option;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\ApiOldBundle\Form\DataTransformer\BooleanFieldTransformer;

class RestOptionChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanFieldTransformer();

        $builder
            ->add($builder->create('has_stock', 'text')->addModelTransformer($transformer))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\OptionChoice',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
