<?php

namespace Clab\BoardBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StaffMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'pro.restaurant.team.nameLabel'))
            ->add('description', null, array('label' => 'pro.restaurant.team.descriptionLabel', 'required' => true))
            ->add('image', null, array('label' => 'pro.restaurant.team.coverLabel', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\StaffMember',
        ));
    }

    public function getName()
    {
        return 'board_store_staffmember';
    }
}
