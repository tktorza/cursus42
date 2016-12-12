<?php

namespace Clab\BoardBundle\Form\Type\Store;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StoreProfileSocialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('staticFacebookId', null, array('label' => 'ID Facebook', 'required' => false))
            ->add('staticTwitterId', null, array('label' => 'ID Twitter', 'required' => false))
            ->add('staticFoursquareId', null, array('label' => 'ID Foursquare', 'required' => false))
            ->add('staticTumblrId', null, array('label' => 'ID Tumblr', 'required' => false))
            ->add('staticInstagramId', null, array('label' => 'ID Instagram', 'required' => false))
            ->add('hashtag', null, array('label' => 'pro.communication.conf.networks.hashtagLabel', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\SocialBundle\Entity\SocialProfile'
        ));
    }

    public function getName()
    {
        return 'board_store_profile_social';
    }
}
