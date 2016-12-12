<?php

namespace Clab\LocationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\LocationBundle\Form\Type\EventScheduleType;
use Clab\SocialBundle\Form\Type\SocialProfileType;

class EventType extends AbstractType
{
    protected $gallery = null;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(isset($options['data']) && $options['data']) {
            if($options['data']->getGallery()) {
                $this->gallery = $options['data']->getGallery();
            }
        }

        $builder
            ->add('name', null, array('label' => 'Nom', 'required' => true))
            ->add('description', null, array('label' => 'Description', 'required' => false))
            ->add('is_online', null, array('label' => 'En ligne', 'required' => false))
            ->add('place', null, array('required' => true, 'label' => 'Lieu'))
            ->add('schedules', 'collection', array(
                'type' => new EventScheduleType(),
                'allow_add' => true,
                'allow_delete' => true,
                'cascade_validation' => true,
                'label' => 'Planning'
            ))
            ->add('socialProfile', new SocialProfileType('classic'), array('label' => 'Réseaux sociaux'))
        ;

        if($this->gallery && count($this->gallery->getImages()) > 0) {
            $builder
                ->add('profile_picture', 'entity', array(
                    'class' => 'Clab\MediaBundle\Entity\Image',
                    'choices' => $this->gallery->getImages(),
                    'expanded' => true,
                    'label' => 'Photo de profil'
                ))
                ->add('cover_picture', 'entity', array(
                    'class' => 'Clab\MediaBundle\Entity\Image',
                    'choices' => $this->gallery->getImages(),
                    'expanded' => true,
                    'label' => 'Photo de couverture'
                ))
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\LocationBundle\Entity\Event',
        ));
    }

    public function getName()
    {
        return 'location_event';
    }
}
