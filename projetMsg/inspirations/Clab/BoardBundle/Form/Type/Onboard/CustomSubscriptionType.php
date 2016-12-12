<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Entity\Subscription;

class CustomSubscriptionType extends AbstractType
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
                    12 => 'Annuel'
                )
            ))
            ->add('type', 'choice', array(
                'label' => 'Offre',
                'required' => true,
                'expanded' => true,
                'choices' => Subscription::getAvailableClassicTypes(),
            ))
            ->add('moduleAppleApp', null, array(
                'label' => 'Je souhaite l\'application iOS',
                'required' => false,
            ))

            ->add('moduleMultisite', null, array(
                'label' => 'Je souhaite un site web',
                'required' => false,
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
        return 'board_onboard_custom_subscription';
    }
}
