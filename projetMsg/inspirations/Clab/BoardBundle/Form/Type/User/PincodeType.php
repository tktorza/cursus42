<?php

namespace Clab\BoardBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PincodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'required' => true,
                'label' => 'Nom',
            ))
            ->add('code', 'text', array(
                'required' => true,
                'label' => 'Code',
            ))
            ->add('hasRightOnBo', 'checkbox', array(
                'required' => false,
                'label' => 'Accèder à myclickeat',
            ))
            ->add('hasRightOnLogs', 'checkbox', array(
                'required' => false,
                'label' => 'Accèder aux logs',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\Pincode',
        ));
    }

    public function getName()
    {
        return 'board_store_pincode';
    }
}
