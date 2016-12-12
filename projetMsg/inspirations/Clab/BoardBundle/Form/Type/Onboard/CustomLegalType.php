<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\LocationBundle\Form\Type\AddressType;

class CustomLegalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('legalType', 'choice', array('label' => 'Type de société', 'required' => true, 'choices' => Restaurant::getLegalTypeChoices()))
            ->add('legalName', null, array('label' => 'Nom de la société', 'required' => false))
            ->add('siret', null, array('label' => 'Numéro SIRET', 'required' => false))
            ->add('legalPerson', null, array('label' => 'Prénom et nom du représentant', 'required' => false))
            ->add('capital', null, array('label' => 'Capital social', 'required' => false))
            ->add('legalAddress', new AddressType(), array(
                'required' => false,
                'label' => 'Votre adresse administrative',
                'constraints' => array(new NotNull())
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_onboard_custom_legal';
    }
}
