<?php

namespace Clab\WhiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array(
                'attr' => array(
                    'placeholder' => 'jaimelessushi@maki.com'
                )
            ))
            ->add('isMale', 'choice', array(
                'choices' => array(
                    0 => 'Mme',
                    1 => 'Mr'
                ),
                'expanded' => false,
                'multiple' => false,
            ))
            ->add('first_name', 'text', array(
                'attr' => array(
                    'placeholder' => 'Prénom'
                )
            ))
            ->add('last_name', 'text', array(
                'attr' => array(
                    'placeholder' => 'Nom'
                )
            ))
            ->add('phone', 'text', array(
                'attr' => array(
                    'placeholder' => '0612345678'
                )
            ))
            ->add('birthday', 'birthday', array(
                    'label' => 'Date de naissance',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'choice',
                    'years' => range(date('Y') -13, date('Y') -100)/*,
                    'placeholder' =>array(
                        'years' => 'Année','months' => 'Mois','days' => 'Jour'
                    )*/
                )
            )
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
        return 'clab_white_user';
    }
}
