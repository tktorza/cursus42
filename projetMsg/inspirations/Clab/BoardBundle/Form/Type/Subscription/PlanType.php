<?php

namespace Clab\BoardBundle\Form\Type\Subscription;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Entity\Subscription;

class PlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billingRecurrency', 'choice', array(
                'label' => 'Paiement',
                'required' => true,
                'expanded' => true,
                'choices' => array(
                    1 => 'Mensuel',
                    12 => 'Annuel',
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\Subscription',
        ));
    }

    public function getName()
    {
        return 'board_subscribe_plans';
    }
}
