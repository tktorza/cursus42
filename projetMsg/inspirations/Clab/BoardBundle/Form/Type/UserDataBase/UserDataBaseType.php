<?php

namespace Clab\BoardBundle\Form\Type\UserDataBase;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDataBaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('firstName', null, array('label' => 'Prénom'))
          ->add('lastName', null, array('label' => 'Nom de famille'))
          ->add('email', null, array('label' => 'Email'))
          ->add('phone', null, array('label' => 'Numéro de téléphone'))
          ->add('day', null, array('label' => 'Anniversaire','mapped' => false))
          ->add('company', null, array('label' => 'Entreprise'))
          ->add('note', null, array('label' => 'Commentaire'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\UserDataBase',
        ));
    }

    public function getName()
    {
        return 'board_user_database';
    }
}
