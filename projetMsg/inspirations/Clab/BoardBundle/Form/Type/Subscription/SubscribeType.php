<?php

namespace Clab\BoardBundle\Form\Type\Subscription;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;

use libphonenumber\PhoneNumberFormat;

class SubscribeType extends AbstractType
{
    protected $type = 'restaurant';

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['type']) && $parameters['type'] == 'foodtruck') {
            $this->type = $parameters['type'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nom du ' . $this->type, 'required' => true))
            ->add('managerEmail', 'email', array('label' => 'Votre email', 'required' => true, 'constraints' => array(new NotBlank(), new Email())))
            ->add('managerPhone', 'tel', array(
                'label' => 'Votre numéro de téléphone',
                'required' => true,
                'constraints' => array(new NotBlank(), new PhoneNumber()),
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL
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
        return 'board_subscribe';
    }
}
