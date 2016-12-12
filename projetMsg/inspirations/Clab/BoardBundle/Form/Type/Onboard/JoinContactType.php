<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;

use libphonenumber\PhoneNumberFormat;

class JoinContactType extends AbstractType
{
    protected $type = null;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['type'])) {
            $this->type = $parameters['type'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('duplicate', 'choice', array(
                'label' => 'J\'ai un problème lors de mon inscription',
                'required' => true,
                'choices' => array('restaurant' => 'Ce n\'est pas mon restaurant', 'manager' => 'Ce n\'est pas mon compte'),
                'multiple' => false,
                'expanded' => true,
                'data' => $this->type,
            ))
            ->add('email', 'email', array('label' => 'Votre email (*)', 'required' => true, 'constraints' => array(new NotBlank(), new Email())))
            ->add('phone', 'tel', array(
                'label' => 'Votre numéro de téléphone (*)',
                'required' => true,
                'constraints' => array(new NotBlank(), new PhoneNumber()),
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL
            ))
            ->add('message', 'textarea', array('label' => 'Message (optionnel)', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getName()
    {
        return 'board_onboard_join_contact';
    }
}
