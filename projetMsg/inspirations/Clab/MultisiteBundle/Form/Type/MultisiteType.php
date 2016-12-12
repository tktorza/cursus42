<?php

namespace Clab\MultisiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\MultisiteBundle\Entity\Multisite;

class MultisiteType extends AbstractType
{
    protected $step = 1;

    public function __construct($step)
    {
        $this->step = $step;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->step == 1) {
            $builder
                ->add('primaryColor', null, array('label' => 'Couleur principale'))
                ->add('secondaryColor', null, array('label' => 'Couleur secondaire'))
                ->add('navigationType', 'choice', array(
                    'label' => 'Navigation',
                    'choices' => Multisite::getNavigationTypes(),
                    'expanded' => true,
                    'required' => true
                ))
                ->add('baseTheme', 'choice', array(
                    'label' => 'Theme',
                    'choices' => Multisite::getThemes(),
                    'expanded' => true,
                    'required' => true
                ))
            ;
        } elseif($this->step == 2) {
            $builder
                ->add('is_online', null, array('label' => 'En ligne', 'required' => false))
                ->add('sectionTeam', null, array('label' => 'Section l\'équipe', 'required' => false))
                ->add('sectionTeamBackground', null, array('label' => 'Couleur de fond', 'required' => false))
                ->add('sectionMenu', null, array('label' => 'Section carte', 'required' => false))
                ->add('sectionMenuBackground', null, array('label' => 'Couleur de fond', 'required' => false))
                ->add('sectionSocial', null, array('label' => 'Section social', 'required' => false))
                ->add('sectionSocialBackground', null, array('label' => 'Couleur de fond', 'required' => false))
                ->add('sectionLocation', null, array('label' => 'Section nous trouver', 'required' => false))
                ->add('sectionLocationBackground', null, array('label' => 'Couleur de fond', 'required' => false))
                ->add('sectionContact', null, array('label' => 'Formulaire de contact', 'required' => false))
                ->add('sectionContactBackground', null, array('label' => 'Couleur de fond', 'required' => false))
                ->add('contactEmail', null, array('label' => 'Email de contact', 'required' => false))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MultisiteBundle\Entity\Multisite',
        ));
    }

    public function getName()
    {
        return 'clab_multisite_site';
    }
}
