<?php

namespace Clab\BoardBundle\Form\Type\Review;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('response', 'textarea', array(
                'label' => 'Réponse',
                'required' => false,
                'attr' => array('cols' => '5', 'rows' => '5'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\Reviewbundle\Entity\Review',
        ));
    }

    public function getName()
    {
        return 'board_review';
    }
}
