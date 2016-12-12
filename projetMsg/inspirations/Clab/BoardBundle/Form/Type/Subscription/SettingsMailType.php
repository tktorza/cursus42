<?php

namespace Clab\BoardBundle\Form\Type\Subscription;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class SettingsMailType extends AbstractType
{
    protected $isMobile = false;

    public function __construct($isMobile = false)
    {
        $this->isMobile = $isMobile;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notification_mails', null, array('label' => 'Email de notification des commandes', 'required' => false))
            ->add('emailPayment', null, array('label' => 'Email de notification de paiement', 'required' => false))
        ;

        if($this->isMobile) {
            $builder
                ->add('tttEventValidationMail', null, array('label' => 'Email de certification d\'emplacement', 'required' => true))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Restaurant',
        ));
    }

    public function getName()
    {
        return 'board_settings_mail';
    }
}
