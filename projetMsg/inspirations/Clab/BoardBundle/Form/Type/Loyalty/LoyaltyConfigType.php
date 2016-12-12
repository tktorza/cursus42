<?php

namespace Clab\BoardBundle\Form\Type\Loyalty;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoyaltyConfigType extends AbstractType
{
    protected $name = false;

    public function __construct($name = false)
    {
        $this->name = $name;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder
           ->add('minimumOrder','number',array('label'=>'Panier minimum'))
           ->add('percentageOfOrder','number',array('label'=>'Pourcentage du panier converti en prime'))
           ->add('validityPeriod','number',array('label'=>'Durée de validité de la prime en jours'))
           ->add('firstValidityPeriod','number',array('label'=>'Durée de validité des primes de création de compte en mois'))
           ->add('refreshPeriod','number',array('label'=>'Durée de validité ajoutée lors du passage en caisse sans utilisation de prime, en jours'))
           ->add('minValue','number',array('label'=>'Valeur de prime minimum'))
           ->add('maxValue','number',array('label'=>'Valeur de prime maximum'))
           ->add('roundRatio','number',array('label'=>'Précision de l\'arrondi'))
       ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\ShopBundle\Entity\LoyaltyConfig',
        ));
    }

    public function getName()
    {
        return 'board_loyalty_config';
    }
}
