<?php

namespace Clab\BoardBundle\Form\Type\Discount;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\ShopBundle\Entity\Discount;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom', 'required' => true))
            ->add('type', 'choice', array(
                'label' => 'Réduction sur',
                'choices' => Discount::getTypeChoices(),
                'expanded' => true,
            ))
            ->add('percent', 'choice', array(
                'label' => 'Réduction',
                'choices' => array(10 => '-10%', 15 => '-15%', 20 => '-20%', 30 => '-30%', 40 => '-40%', 50 => '-50%'),
                'expanded' => true,
            ))
            ->add('isMultisite', null, array('label' => 'Marques blanches',))
            ->add('isOnline', null, array('label' => 'En ligne',))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\ShopBundle\Entity\Discount',
        ));
    }

    public function getName()
    {
        return 'board_discount';
    }
}
