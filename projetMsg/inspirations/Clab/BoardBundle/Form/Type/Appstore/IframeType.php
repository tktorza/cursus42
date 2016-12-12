<?php

namespace Clab\BoardBundle\Form\Type\Appstore;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IframeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_online', null, array('label' => 'Mettre en ligne', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MultisiteBundle\Entity\Multisite',
        ));
    }

    public function getName()
    {
        return 'board_appstore_iframe';
    }
}
