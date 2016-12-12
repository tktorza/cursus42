<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\DataTransformer\BooleanIntegerTransformer;

class CustomOrderDealType extends AbstractType
{
    protected $isMobile = false;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['isMobile']) && is_bool($parameters['isMobile'])) {
            $this->isMobile = $parameters['isMobile'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanIntegerTransformer();

        $builder
            ->add($builder->create('interestedInOrder', 'checkbox', array('label' => 'Je souhaiterais proposer de la commande en ligne', 'required' => false))->addModelTransformer($transformer))
            ->add($builder->create('interestedInTakeaway', 'checkbox', array('label' => 'A emporter', 'required' => false))->addModelTransformer($transformer))
            ->add($builder->create('interestedInDelivery', 'checkbox', array('label' => 'En livraison', 'required' => false))->addModelTransformer($transformer))
        ;

        if($this->isMobile) {
            $builder
                ->add($builder->create('interestedInEmbedOrder', 'checkbox', array('label' => 'que mes clients puissent commander sur mon site web', 'required' => false))->addModelTransformer($transformer))
                ->add($builder->create('interestedInFacebookOrder', 'checkbox', array('label' => 'que mes clients puissent commander sur ma page Facebook', 'required' => false))->addModelTransformer($transformer))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\PanelBundle\Entity\Deal',
        ));
    }

    public function getName()
    {
        return 'board_onboard_custom_order_deal';
    }
}
