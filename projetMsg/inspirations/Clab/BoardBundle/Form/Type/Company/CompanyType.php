<?php

namespace Clab\BoardBundle\Form\Type\Company;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

use Clab\LocationBundle\Form\Type\AddressType;

class CompanyType extends AbstractType
{
    private $edit;
    public function __construct($edit = false)
    {
        $this->edit = $edit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', new AddressType(), array(
                'required' => false,
                'label' => 'Adresse',
            ))
            ->add('email', 'email', array('label' => 'Email', 'constraints' => array(new Email(),
                new NotBlank(), ), 'required' => false))
            ->add('phone', 'text', array(
                'label' => 'Téléphone',
                'required' => true
            ))
            ->add('name', null, array('label' => 'Nom', 'required' => true))
            ->add('companyPayment', 'choice', array(
                'label' => 'moyen de paiement',
                'required' => true,
                'choices' => array( 'Rib' => 'Rib',
                                    'Cheque' => 'Cheque',
                                    'Virement' => 'Virement')
                )
            )
            ->add('isOnline',null,array('label'=>'En ligne  '))
            ->add('description', null, array('label' => 'Description', 'required' => false))
            ->add('accountCode', null, array('label' => 'code compte', 'required' => true))
        ;

        if ($this->edit) {
           $builder
               ->add('nextDueDate', 'date', array('label' => 'Prochain paiement', 'required' => false))
               ->add('balance', null, array('label' => 'Balance', 'required' => false, 'read_only' => true ))
           ;
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\Company',
        ));
    }

    public function getName()
    {
        return 'client_company';
    }
}
