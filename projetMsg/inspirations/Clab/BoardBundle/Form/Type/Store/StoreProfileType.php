<?php

namespace Clab\BoardBundle\Form\Type\Store;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\LocationBundle\Form\Type\AddressType;
use Clab\BoardBundle\Form\Type\Store\StoreProfileSocialType;

use Symfony\Component\Validator\Constraints\Email;

use libphonenumber\PhoneNumberFormat;

class StoreProfileType extends AbstractType
{
    protected $categories = array();
    protected $extraCategories = array();
    protected $isMobile = false;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['categories']) && is_array($parameters['categories'])) {
            $this->categories = $parameters['categories'];
        }

        if(isset($parameters['extraCategories']) && is_array($parameters['extraCategories'])) {
            $this->extraCategories = $parameters['extraCategories'];
        }

        if(isset($parameters['isMobile']) && is_bool($parameters['isMobile'])) {
            $this->isMobile = $parameters['isMobile'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', null, array('label' => 'pro.restaurant.infos.descriptionLabel', 'required' => false))
            ->add('small_description', null, array('label' => 'pro.restaurant.infos.smallDescriptionLabel', 'required' => false))
            ->add('phone', 'tel', array('label' => 'Téléphone (*)', 'required' => false,'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL))
            ->add('email', 'email', array('label' => 'Email (*)', 'required' => false, 'constraints' => array(new Email())))
            ->add('website', null, array('label' => 'Website', 'required' => false))
            ->add('siret', null, array('label' => 'SIRET', 'required' => false))
            ->add('accountCode', null, array('label' => 'codeComptable', 'required' => false))

            ->add('tags', null, array(
                'choices' => $this->categories,
                'label' => 'Catégorie (3 maximum)'
            ))

            ->add('extraTags', null, array(
                'choices' => $this->extraCategories,
                'label' => 'Catégorie Bonus'
            ))

            ->add('services', null, array(
                'label' => 'pro.restaurant.store.store.servicesLabel', 
                'required' => false
            ))

            ->add('storePaymentMethods', null, array('label' => 'pro.restaurant.store.store.paymentMethodsLabel', 'required' => false, 'expanded' => true))

            ->add('socialProfile', new StoreProfileSocialType(), array(
                'required' => true,
                'label' => 'Social',
            ))
        ;

        if(!$this->isMobile) {
            $builder->add('address', new AddressType(), array(
                'required' => false,
                'label' => 'pro.address.title',
            ));
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
        return 'board_store_profile';
    }
}
