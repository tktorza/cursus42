<?php

namespace Clab\BoardBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocialPostType extends AbstractType
{
    protected $shareSocialNetworks = false;
    protected $isOnline = false;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['share_social_networks'])) {
            $this->shareSocialNetworks = $parameters['share_social_networks'];
        }

        if(isset($parameters['is_online'])) {
            $this->isOnline = $parameters['is_online'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('label' => 'Titre', 'required' => true))
            ->add('message', null, array('label' => 'Message', 'required' => true))
            ->add('image', 'file', array('label' => 'Image', 'required' => false))
        ;

        if($this->shareSocialNetworks) {
            $builder
                ->add('to_facebook', 'checkbox', array('mapped' => false, 'label' => 'Partage Facebook', 'required' => false))
                ->add('to_twitter', 'checkbox', array('mapped' => false, 'label' => 'Partage Twitter', 'required' => false))
            ;

            if($this->isOnline) {
                $builder->add('add_link', 'checkbox', array('label' => 'pro.communication.post.link', 'required' => false, 'data' => true, 'mapped' => false));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\SocialBundle\Entity\SocialPost',
        ));
    }

    public function getName()
    {
        return 'board_social_post';
    }
}
