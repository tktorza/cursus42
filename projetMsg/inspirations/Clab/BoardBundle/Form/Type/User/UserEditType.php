<?php

namespace Clab\BoardBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\UserBundle\Entity\User;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data'])) {
            $user = $options['data'];
        }
        $builder
            ->add('firstName', 'text', array(
                'required' => true,
                'label' => 'Prénom',
            ))
            ->add('lastName', 'text', array(
                'required' => true,
                'label' => 'Nom',
            ))
                ->add('roles', 'choice', array(
                    'choices' => User::getManagerRoles(),
                    'data' => $user ? array_intersect($user->getRoles(), array_keys(User::getManagerRoles())) : array(),
                    'mapped' => false,
                    'expanded' => true,
                    'multiple' => true,
                ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'board_store_user';
    }
}
