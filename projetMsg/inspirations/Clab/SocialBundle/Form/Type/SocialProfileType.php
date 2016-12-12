<?php

namespace Clab\SocialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocialProfileType extends AbstractType
{
    protected $type = 'classic';

    public function __construct($type = 'classic') {
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->type == 'classic') {
            $builder
                ->add('staticFacebookId', null, array('label' => 'ID Facebook', 'required' => false))
                ->add('staticTwitterId', null, array('label' => 'ID Twitter', 'required' => false))
                ->add('staticFoursquareId', null, array('label' => 'ID Foursquare', 'required' => false))
                ->add('staticTumblrId', null, array('label' => 'ID Tumblr', 'required' => false))
                ->add('staticInstagramId', null, array('label' => 'ID Instagram', 'required' => false))
                ->add('hashtag', null, array('label' => 'pro.communication.conf.networks.hashtagLabel', 'required' => false))
            ;
        } elseif($this->type == 'ttt') {
            $builder
                ->add('tttEventValidationMessage', null, array('label' => 'Message de validation d\'évènement', 'required' => false))
                ->add('tttEventAnnulationMessage', null, array('label' => 'Message de d\'annulation d\'évènement', 'required' => false))
                ->add('tttEventToFacebook', null, array('label' => 'pro.communication.conf.ttt.facebookLabel', 'required' => false))
                ->add('tttEventToTwitter', null, array('label' => 'pro.communication.conf.ttt.twitterLabel', 'required' => false))
            ;
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Clab\SocialBundle\Entity\SocialProfile'
            ))
        ;
    }

    public function getName()
    {
       return 'clab_social_profile';
    }
}