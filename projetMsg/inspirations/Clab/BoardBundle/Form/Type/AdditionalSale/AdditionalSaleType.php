<?php

namespace Clab\BoardBundle\Form\Type\AdditionalSale;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalSaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom', 'required' => true));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\AdditionalSale',
        ));
    }

    public function getName()
    {
        return 'board_additional_sale';
    }
}
