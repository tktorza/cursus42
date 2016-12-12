<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\DataTransformer\BooleanIntegerTransformer;

class CustomInfosDealType extends AbstractType
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
            ->add($builder->create('interestedInWebsite', 'checkbox', array('label' => 'avoir un site web', 'required' => false))->addModelTransformer($transformer))
            ->add($builder->create('interestedInApp', 'checkbox', array('label' => 'avoir une application iPhone pour mon ' . ($this->isMobile ? 'foodtruck' : 'restaurant'), 'required' => false))->addModelTransformer($transformer))
        ;

        if(!$this->isMobile) {
            $builder
                ->add($builder->create('interestedInEmbedOrder', 'checkbox', array('label' => 'que mes clients puissent commander sur mon site web', 'required' => false))->addModelTransformer($transformer))
                ->add($builder->create('interestedInFacebookOrder', 'checkbox', array('label' => 'que mes clients puissent commander sur ma page Facebook', 'required' => false))->addModelTransformer($transformer))
            ;
        } else {
            $builder
                ->add($builder->create('interestedInFoodtruckEmbed', 'checkbox', array('label' => 'avoir mon menu sur mon site, toujours à jour', 'required' => false))->addModelTransformer($transformer))
                ->add($builder->create('interestedInFoodtruckEmbedFacebook', 'checkbox', array('label' => 'avoir mon menu sur Facebook, toujours à jour', 'required' => false))->addModelTransformer($transformer))
                ->add($builder->create('interestedInFoodtruckPlanningEmbed', 'checkbox', array('label' => 'avoir mon itinéraire sur mon site, toujours à jour', 'required' => false))->addModelTransformer($transformer))
                ->add($builder->create('interestedInFoodtruckPlanningEmbedFacebook', 'checkbox', array('label' => 'avoir mon itinéraire sur Facebook, toujours à jour', 'required' => false))->addModelTransformer($transformer))
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
        return 'board_onboard_custom_info_deal';
    }
}
