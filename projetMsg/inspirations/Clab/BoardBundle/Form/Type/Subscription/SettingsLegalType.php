<?php

namespace Clab\BoardBundle\Form\Type\Subscription;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Email;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;
use libphonenumber\PhoneNumberFormat;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\LocationBundle\Form\Type\AddressType;

class SettingsLegalType extends AbstractType
{
    protected $manager = true;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['manager'])) {
            $this->manager = $parameters['manager'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('legalType', 'choice', array('label' => 'Type de société', 'required' => true, 'choices' => Restaurant::getLegalTypeChoices()))
            ->add('legalName', null, array('label' => 'Nom de la société', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('siret', null, array('label' => 'Numéro SIRET', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('legalPerson', null, array('label' => 'Prénom et nom du représentant', 'required' => true, 'constraints' => array(new NotBlank())))
            ->add('capital', null, array('label' => 'Capital social', 'required' => false))
            ->add('legalAddress', new AddressType(), array(
                'required' => true,
                'label' => 'Adresse siège social',
                'constraints' => array(new NotNull())
            ))
        ;

        if($this->manager) {
            $builder
                ->add('managerFirstName', null, array('label' => 'Prénom', 'required' => true, 'constraints' => array(new NotBlank())))
                ->add('managerName', null, array('label' => 'Nom', 'required' => true, 'constraints' => array(new NotBlank())))
                ->add('managerEmail', 'email', array('label' => 'Email', 'required' => true, 'constraints' => array(new Email())))
                ->add('managerPhone', 'tel', array('label' => 'Téléphone', 'required' => true, 'constraints' => array(new PhoneNumber()),
                    'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_settings_legal';
    }
}
