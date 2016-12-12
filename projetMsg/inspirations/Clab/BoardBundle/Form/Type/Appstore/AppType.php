<?php

namespace Clab\BoardBundle\Form\Type\Appstore;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\MultisiteBundle\Entity\MultiApp;

class AppType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('homeAppColor', null, array('label' => 'Couleur home'))
            ->add('defaultAppColor', null, array('label' => 'Couleur principale'))
            ->add('defaultTitleColor', null, array('label' => 'Couleur titre'))

            ->add('homeVersion', 'choice', array(
                'label' => 'Page d\'accueil',
                'choices' => MultiApp::getHomeVersionChoices(),
                'required' => true, 'multiple' => false,
            ))

            ->add('catalogVersion', 'choice', array(
                'label' => 'Type de carte',
                'choices' => MultiApp::getCatalogVersionChoices(),
                'required' => true, 'multiple' => false,
            ))

            ->add('profileVersion', 'choice', array(
                'label' => 'Profil du restaurant',
                'choices' => MultiApp::getProfileVersionChoices(),
                'required' => true, 'multiple' => false,
            ))

            ->add('newsVersion', 'choice', array(
                'label' => 'Type de news',
                'choices' => MultiApp::getNewsVersionChoices(),
                'required' => true, 'multiple' => false,
            ))

            ->add('logo', null, array('label' => 'Télécharger un logo', 'required' => false))
            ->add('background', null, array('label' => 'Télécharger un background', 'required' => false))

            ->add('hasModuleOrder', null, array('label' => 'Activer la commande', 'required' => false))
            ->add('hasModuleDelivery', null, array('label' => 'Activer la livraison', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MultisiteBundle\Entity\MultiApp',
        ));
    }

    public function getName()
    {
        return 'board_appstore_app';
    }
}
