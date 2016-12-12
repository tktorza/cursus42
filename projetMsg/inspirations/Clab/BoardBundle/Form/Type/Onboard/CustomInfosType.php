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

use Clab\BoardBundle\Form\Type\Onboard\CustomInfosDealType;

class CustomInfosType extends AbstractType
{
    protected $categories = array();
    protected $isMobile = false;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['categories']) && is_array($parameters['categories'])) {
            $this->categories = $parameters['categories'];
        }

        if(isset($parameters['isMobile']) && is_bool($parameters['isMobile'])) {
            $this->isMobile = $parameters['isMobile'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'E-mail du restaurant', 'required' => false, 'constraints' => array(new NotBlank(), new Email())))
            ->add('phone', 'tel', array(
                'label' => 'Numéro de téléphone du restaurant',
                'required' => false,
                'constraints' => array(new NotBlank(), new PhoneNumber()),
                'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL
            ))
            ->add('description', null, array('label' => 'Description du restaurant', 'required' => false))
            ->add('small_description', null, array('label' => 'Slogan', 'required' => false))

            ->add('tags', null, array(
                'choices' => $this->categories,
                'label' => 'Type de restauration (3 maximum)',
            ))

            ->add('deal', new CustomInfosDealType(array('isMobile' => $this->isMobile)))
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
        return 'board_onboard_custom_info';
    }
}
