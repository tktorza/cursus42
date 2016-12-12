<?php

namespace Clab\BoardBundle\Form\Type\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocialPostEventType extends AbstractType
{
    protected $events = array();

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('event', 'choice', array(
            'label' => 'Evènement',
            'required' => true,
            'choices' => $this->events,
            'expanded' => true,
            'data' => isset($this->events[0]) ? $this->events[0] : null,
            'mapped' => false
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\SocialBundle\Entity\SocialPost',
        ));
    }

    public function getName()
    {
        return 'board_social_post_event';
    }
}
