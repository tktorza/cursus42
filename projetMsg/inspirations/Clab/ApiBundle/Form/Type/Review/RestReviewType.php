<?php

namespace Clab\ApiBundle\Form\Type\Review;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Clab\ReviewBundle\Entity\Review;

class RestReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array('constraints' => new NotBlank()))
            ->add('body', null, array('constraints' => new NotBlank()))
            ->add('score', 'choice', array(
                'choices' => Review::getAvailableScores(),
                'constraints' => new NotBlank(),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
