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

use Clab\BoardBundle\Form\Type\Onboard\JoinStoreDealType;

class JoinStoreType extends AbstractType
{
    protected $name = true;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['name'])) {
            $this->name = $parameters['name'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(isset($options['data'])) {
            $restaurant = $options['data'];

            if($restaurant->getId()) {
                $this->name = false;
            }
        }

        if($this->name) {
            $builder->add('name', null, array('label' => 'Nom du restaurant (*)', 'required' => true));
        }

        $builder
            ->add('managerEmail', 'email', array('label' => 'Votre email (*)', 'required' => true, 'constraints' => array(new NotBlank(), new Email())))
            ->add('managerPhone', 'tel', array(
                'label' => 'Votre numéro de téléphone (*)',
                'required' => true,
                'constraints' => array(new NotBlank(), new PhoneNumber()),
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL
            ))
            ->add('deal', new JoinStoreDealType())
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
        return 'board_onboard_join_store';
    }
}
