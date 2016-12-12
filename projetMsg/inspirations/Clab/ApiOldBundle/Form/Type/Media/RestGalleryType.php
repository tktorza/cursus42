<?php

namespace Clab\ApiOldBundle\Form\Type\Media;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RestGalleryType extends AbstractType
{
    protected $images = array();

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['images'])) {
            $this->images = $parameters['images'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cover', 'entity', array(
                'class' => 'Clab\MediaBundle\Entity\Image',
                'choices' => $this->images,
                'multiple' => false,
                'mapped' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MediaBundle\Entity\Gallery',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
