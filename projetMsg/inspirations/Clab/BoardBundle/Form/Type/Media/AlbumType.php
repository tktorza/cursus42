<?php

namespace Clab\BoardBundle\Form\Type\Media;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom', 'required' => true))
            ->add('description', null, array('label' => 'Description'))
            ->add('is_online', null, array('required' => false, 'label' => 'En ligne'))
            ->add('isClickeat', null, array('required' => false, 'label' => 'Publié sur Clickeat'))
            ->add('isTTT', null, array('required' => false, 'label' => 'Publié sur TTT'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MediaBundle\Entity\Album',
        ));
    }

    public function getName()
    {
        return 'board_album';
    }
}
