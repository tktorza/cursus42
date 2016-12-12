<?php

namespace Clab\BoardBundle\Form\Type\Appstore;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\MultisiteBundle\Entity\Multisite;

class WebsiteType extends AbstractType
{
    protected $images = array();

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['images'])) {
            $this->images = $parameters['images'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_online', null, array('label' => 'Mettre en ligne', 'required' => false))
            ->add('sectionMenu', null, array('label' => 'Afficher', 'required' => false))
            ->add('sectionSocial', null, array('label' => 'Afficher', 'required' => false))
            ->add('sectionContact', null, array('label' => 'Afficher', 'required' => false))
            ->add('sectionReview', null, array('label' => 'Afficher', 'required' => false))
            ->add('sectionGallery', null, array('label' => 'Afficher', 'required' => false))

            ->add('aboutTitle', null, array('label' => 'Titre bloc description', 'required' => false))
            ->add('orderButton', null, array('label' => 'Bouton commande en ligne', 'required' => false))
            ->add('logo', null, array('label' => 'Logo', 'required' => false))

            ->add('coverPicture', 'entity', array(
                'label' => 'Choisir une photo de couverture',
                'class' => 'Clab\MediaBundle\Entity\Image',
                'choices' => $this->images,
                'required' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MultisiteBundle\Entity\Multisite',
        ));
    }

    public function getName()
    {
        return 'board_appstore_website';
    }
}
