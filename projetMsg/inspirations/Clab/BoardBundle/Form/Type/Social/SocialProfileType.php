<?php

namespace Clab\BoardBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocialProfileType extends AbstractType
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
        $builder->add('facebookSynch', null, array('label' => 'Synchronisation Facebook', 'required' => false));

        if($this->isMobile) {
            $builder
                ->add('tttEventValidationMessage', null, array('label' => 'Message de validation d\'évènement', 'required' => false))
                ->add('tttEventAnnulationMessage', null, array('label' => 'Message de d\'annulation d\'évènement', 'required' => false))
                ->add('tttEventToFacebook', null, array('label' => 'pro.communication.conf.ttt.facebookLabel', 'required' => false))
                ->add('tttEventToTwitter', null, array('label' => 'pro.communication.conf.ttt.twitterLabel', 'required' => false))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\SocialBundle\Entity\SocialProfile',
        ));
    }

    public function getName()
    {
        return 'board_social_profile_foodtruck';
    }
}
