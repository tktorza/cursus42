<?php

namespace Clab\BoardBundle\Form\Type\AdditionalSale;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalSaleEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom', 'required' => true))
            ->add('multiple', 'checkbox', array('label' => 'choix multiple'))
            ->add('isOnline', 'checkbox', array('label' => 'En Ligne'))
            ->add('minimum', 'integer', array('label' => 'choix minimum', 'required' => false, 'attr' => array('min' => 0)))
            ->add('maximum', 'integer', array('label' => 'choix maximum', 'required' => false, 'attr' => array('min' => 0)))
            ->add('additionalSaleProducts', 'collection', array(
                'type' => new AdditionalSaleProductType(),
                'options' => array(
                    'required' => false,
                ),
                'label' => false,
            ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\AdditionalSale',
        ));
    }

    public function getName()
    {
        return 'board_additional_sale_edit';
    }
}
