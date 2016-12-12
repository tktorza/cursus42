<?php

namespace Clab\BoardBundle\Form\Type\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('forcedPricing', null, array('label' => 'Bloquer gestion des prix', 'required' => false))
            ->add('forcedAdd', null, array('label' => 'Bloquer l\'ajout et l\'édition de produits', 'required' => false))
            ->add('forcedIsOnline', null, array('label' => 'Bloquer gestion des produits en ligne/hors ligne', 'required' => false))
            ->add('logoFile',
                'vich_image',
                array(
                    'required' => false,
                    'label' => 'Logo de l\'enseigne',
                    'allow_delete' => true, // not mandatory, default is true
                    'download_link' => false, // not mandatory, default is true
                ));
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\Client',
        ));
    }

    public function getName()
    {
        return 'client_settings';
    }
}
