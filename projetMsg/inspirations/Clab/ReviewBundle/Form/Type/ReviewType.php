<?php

namespace Clab\ReviewBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', 'textarea', array(
                'attr' => array('cols' => '5', 'rows' => '5'),
                'required' => true,
            ))
            ->add('title', 'text', array(
                'required' => false,
            ))
            ->add('url', 'text', array(
                'required' => false,
            ))
            ->add('isRecommended', 'checkbox', array(
                'required' => false,
                'attr' => array('checked' => 'checked'),
            ))
            ->add('cookScore', 'choice', array(
                'choices' => array(
                    '2' => 'Très mauvais',
                    '4' => 'Mauvais',
                    '6' => 'Moyen',
                    '8' => 'Bien',
                    '10' => 'Excellent',
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => 'Qualité des plats',
            ))
            ->add('serviceScore', 'choice', array(
                'choices' => array(
                    '2' => 'Très mauvais',
                    '4' => 'Mauvais',
                    '6' => 'Moyen',
                    '8' => 'Bien',
                    '10' => 'Excellent',
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => 'Service',

            ))
            ->add('hygieneScore', 'choice', array(
                'choices' => array(
                    '2' => 'Très mauvais',
                    '4' => 'Mauvais',
                    '6' => 'Moyen',
                    '8' => 'Bien',
                    '10' => 'Excellent',
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => 'Propreté',
            ))

            ->add('qualityScore', 'choice', array(
                'choices' => array(
                    '2' => 'Très mauvais',
                    '4' => 'Mauvais',
                    '6' => 'Moyen',
                    '8' => 'Bien',
                    '10' => 'Excellent',
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => 'Prix',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\ReviewBundle\Entity\Review',
        ));
    }

    public function getName()
    {
        return 'clickeat_review';
    }
}
