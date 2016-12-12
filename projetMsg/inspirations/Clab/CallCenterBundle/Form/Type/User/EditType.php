<?php

namespace Clab\CallCenterBundle\Form\Type\User;

use Clab\BoardBundle\Repository\CompanyRepository;
use Clab\LocationBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class EditType extends AbstractType
{

    protected $orderForm;

    public function __construct($orderForm = false)
    {
        $this->orderForm= $orderForm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', null, array('required' => true))
            ->add('last_name', null, array('required' => true))
            ->add('email', 'email', array('label' => 'clickeat.form.label.email', 'constraints' => array(new Email(),
                new NotBlank(), ), 'required' => false))
        ;

        if (!$this->orderForm) {
            $builder
                ->add('birthday', 'birthday', array(
                        'label' => 'Date de naissance',
                        'input' => 'datetime',
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'choice',
                        'years' => range(date('Y') -13, date('Y') -100),
                        'placeholder' =>array(
                            'years' => 'Année','months' => 'Mois','days' => 'Jour'
                        )
                    )
                )
                ->add('company', 'entity', array(
                    'label' => 'Société',
                    'required' => false,
                    'class' => 'ClabBoardBundle:Company',
                    'query_builder' => function (CompanyRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->where('t.isOnline = true')
                            ->orderBy('t.name', 'asc');
                    },
                ))
                ->add('phone', 'text', array(
                    'label' => 'Téléphone',
                    'required' => true
                ))
                ->add('addresses', 'collection', array(
                    'type' => new AddressType(false,true),
                    'allow_add'    => true,
                    'label' => " "
                ))
                ->add('is_male', 'choice', array(
                    'choices'  => array(
                        'Homme' => true,
                        'Femme' => false,
                    ),
                    'choices_as_values' => true,
                    'label' => 'Sexe',
                    'required' => true
                ))
                ->add('admin_comment','textarea',array('label' => 'commentaire', 'required' => false))
            ;
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'clab_call_center_register';
    }
}
