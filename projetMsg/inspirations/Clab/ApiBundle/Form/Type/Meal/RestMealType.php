<?php

namespace Clab\ApiBundle\Form\Type\Meal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\ApiBundle\Form\DataTransformer\BooleanFieldTransformer;
use Clab\RestaurantBundle\Repository\TaxRepository;

class RestMealType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanFieldTransformer();

        $builder
            ->add('name')
            ->add('description')
            ->add($builder->create('isOnline', 'text')->addModelTransformer($transformer))
            ->add('price')
            ->add('tax', 'entity', array(
                'class' => 'ClabRestaurantBundle:Tax',
                'query_builder' => function (TaxRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.is_online = true')
                        ->orderBy('t.rank', 'asc');
                },
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Meal',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
