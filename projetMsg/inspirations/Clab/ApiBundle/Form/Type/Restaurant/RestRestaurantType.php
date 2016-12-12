<?php

namespace Clab\ApiBundle\Form\Type\Restaurant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestRestaurantType extends AbstractType
{

    public function __construct(array $parameters = array())
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('caisseDiscountsLabels', null, array('required' => false))
            ->add('caisseTags', null, array('required' => false))
            ->add('caissePrinterLabels', null, array('required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
