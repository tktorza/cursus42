<?php

namespace Clab\BoardBundle\Form\Type\Coupon;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom', 'required' => true));
        if (!is_null($options['data']->getAmount())) {
            $builder->add('amount', 'integer', array('label' => 'Taux de réduction', 'required' => false,
                'data' => $options['data']->getAmount(),
            ));
            $builder->add('type', 'choice', array(
                'label' => 'Type de réduction',
                'choices' => array('percent' => 'Pourcentage', 'amount' => 'Prix fixe'),
                'required' => true,
                'data' => 'amount',
                'mapped' => false,
            ));
        } elseif (!is_null($options['data']->getPercent())) {
            $builder->add('amount', 'integer', array('label' => 'Taux de réduction', 'required' => false,
                'data' => $options['data']->getPercent(),
            ));
            $builder->add('type', 'choice', array(
                'label' => 'Type de réduction',
                'choices' => array('percent' => 'Pourcentage', 'amount' => 'Prix fixe'),
                'required' => true,
                'data' => 'percent',
                'mapped' => false,
            ));
        } else {
            $builder->add('amount', 'integer', array('label' => 'Taux de réduction', 'required' => false,
                'data' => 0,
            ));
            $builder->add('type', 'choice', array(
                'label' => 'Type de réduction',
                'choices' => array('percent' => 'Pourcentage', 'amount' => 'Prix fixe'),
                'required' => true,
                'data' => 'percent',
                'mapped' => false,
            ));
        }

        $builder->add('quantity', null, array('label' => 'Quantité', 'required' => false))
            ->add('unlimited', 'checkbox', array('label' => 'Quantité illimitée', 'required' => false))
            ->add('isOnline', null, array('label' => 'En ligne', 'required' => false))
            ->add('startDay', 'date', array(
                'label' => 'Date de début de validité',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))

            ->add('endDay', 'date', array(
                'label' => 'Date de fin de validité',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))
            ->add('platform', 'choice', array(
                'label' => 'Disponible sur',
                'choices' => array(0 => 'Click-eat', 10 => 'Caisse', 20 => 'Les deux plateformes'),
                'required' => true,
            ))
            ->add('isUniqueUsage', 'checkbox', array(
                'label' => 'Usage unique par utilisateur',
                'required' => false,
                'attr' => array('checked' => 'checked'),
            ))

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\ShopBundle\Entity\Coupon',
        ));
    }

    public function getName()
    {
        return 'board_coupon';
    }
}
