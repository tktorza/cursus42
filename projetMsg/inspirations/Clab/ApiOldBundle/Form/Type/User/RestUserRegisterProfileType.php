<?php

namespace Clab\ApiOldBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use libphonenumber\PhoneNumberFormat;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;
use Clab\ApiOldBundle\Form\DataTransformer\BooleanFieldTransformer;

class RestUserRegisterProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanFieldTransformer();

        $builder
            ->add('first_name', null, array('constraints' => new NotBlank()))
            ->add('last_name', null, array('constraints' => new NotBlank()))
            ->add('phone', 'tel', array(
                'constraints' => array(new PhoneNumber()),
                'default_region' => 'FR',
                'format' => PhoneNumberFormat::NATIONAL
            ))
            ->add('zipcode')
            ->add($builder->create('newsletterClickeat', 'text')->addModelTransformer($transformer))
            ->add($builder->create('newsletterTTT', 'text')->addModelTransformer($transformer))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\User',
            'constraints' => array(
                new UniqueEntity(array('fields' => array('phone'), 'message' => 'Ce numéro de téléphone est déjà enregistré'))),
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
