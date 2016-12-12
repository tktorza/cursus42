<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomCatalogDealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('menuSent', null, array('label' => 'J\'ai envoyé ma carte', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\PanelBundle\Entity\Deal',
        ));
    }

    public function getName()
    {
        return 'board_onboard_custom_catalog_deal';
    }
}
