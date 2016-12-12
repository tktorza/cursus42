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

use Clab\BoardBundle\Form\Type\Onboard\CustomOrderDealType;
use Clab\ShopBundle\Entity\OrderTypeRepository;

class CustomOrderType extends AbstractType
{
    protected $isMobile = false;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['isMobile']) && is_bool($parameters['isMobile'])) {
            $this->isMobile = $parameters['isMobile'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deal', new CustomOrderDealType(array('isMobile' => $this->isMobile)))
            ->add('notification_mails', null, array('label' => 'Email de notification des commandes', 'required' => false))
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
        return 'board_onboard_custom_order';
    }
}
