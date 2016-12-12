<?php

namespace Clab\BoardBundle\Form\Type\Appstore;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\ShopBundle\Repository\PaymentMethodRepository;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paymentMethods', 'entity', array(
                'label' => 'Moyens de paiement',
                'required' => true, 'expanded' => true, 'multiple' => true,
                'class' => 'ClabShopBundle:PaymentMethod',
                'query_builder' => function (PaymentMethodRepository $er) {
                    return $er->createQueryBuilder('p');
                },
            ))
            ->add('primaryColor', null, array('label' => 'Couleur principale'))
            ->add('cartColor', null, array('label' => 'Couleur titre panier'))
            ->add('buttonColor', null, array('label' => 'Couleur des boutons'))
            ->add('logoMcFile', 'file', array('label' => 'Logo de votre module de commande'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_appstore_settings';
    }
}
