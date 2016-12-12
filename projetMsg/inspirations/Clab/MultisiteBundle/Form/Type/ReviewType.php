<?php

namespace Clab\MultisiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Clab\ReviewBundle\Entity\Review;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authorName', null, array('label' => 'Votre prénom et nom', 'required' => true, 'constraints' => new NotBlank()))
            ->add('title', null, array('label' => 'clickeat.form.label.title', 'required' => true, 'constraints' => new NotBlank()))
            ->add('body', null, array('label' => 'clickeat.form.label.your_review', 'required' => true, 'constraints' => new NotBlank()))
            ->add('score', 'choice', array(
                'choices' => Review::getAvailableScores(),
                'constraints' => new NotBlank(),
                'data' => 5
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
        return 'multisite_review';
    }
}
