<?php

namespace Clab\BoardBundle\Form\Type\Foodtruck;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\Type\Planning\TimesheetPreorderType;
use Clab\ShopBundle\Entity\PaymentMethodRepository;

class SettingsOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isOpen', 'checkbox', array('label' => 'Activer la commande à emporter', 'required' => false))
            ->add('orderStart', 'choice', array('label' => 'Heure max de commande', 'required' => false,
                'choices' => array(
                    null => 'Aucune',
                    0 => 'Au début de l\'évènement',
                    -1 => '1 heure avant la fin de l\'évènement',
                )
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_foodtruck_settings_order';
    }
}
