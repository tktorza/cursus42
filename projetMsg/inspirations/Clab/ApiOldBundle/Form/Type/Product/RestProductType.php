<?php

namespace Clab\ApiOldBundle\Form\Type\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Clab\ApiOldBundle\Form\DataTransformer\BooleanFieldTransformer;
use Clab\RestaurantBundle\Repository\TaxRepository;

class RestProductType extends AbstractType
{
    protected $categories = array();

    public function __construct(array $parameters = array())
    {
        if (isset($parameters['categories'])) {
            $this->categories = $parameters['categories'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanFieldTransformer();

        $builder
            ->add('name', null, array('required' => false))
            ->add('description')
            ->add($builder->create('is_online', 'text')->addModelTransformer($transformer))
            ->add('price', null, array('required' => false))
            ->add('category', null, array(
                'choices' => $this->categories,
            ))
            ->add('cover', 'file', array('mapped' => false))
            ->add('tax', 'entity', array(
                'class' => 'ClabRestaurantBundle:Tax',
                'query_builder' => function (TaxRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.is_online = true')
                        ->orderBy('t.rank', 'asc');
                },
            ))
            ->add($builder->create('unlimitedStock', 'text')->addModelTransformer($transformer), null, array('required' => false))
            ->add('defaultStock')
            ->add('isPDJ')
            ->add('startDate')
            ->add('endDate')
            ->add('stock', null, array('required' => false))
            ->add('sale', 'number', array('mapped' => false, 'constraints' => array(new Range(array('min' => 0, 'max' => 100)))))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Product',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
