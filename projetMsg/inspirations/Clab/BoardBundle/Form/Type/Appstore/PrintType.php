<?php

namespace Clab\BoardBundle\Form\Type\Appstore;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autoPrint', null, array('label' => 'Impression automatique des tickets à réception d\'une commande'))
            ->add('footerPrint',
                'ckeditor',
                array(
                    'label' => 'Bas de page du ticket d\'impression',
                    'required' => false,
                    'config_name' => 'simple',
                ))
            ->add('printImageFile',
                'vich_image',
                array(
                    'required' => false,
                    'label' => 'Logo en haut du ticket',
                    'allow_delete' => true, // not mandatory, default is true
                    'download_link' => false, // not mandatory, default is true
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_appstore_print';
    }
}
