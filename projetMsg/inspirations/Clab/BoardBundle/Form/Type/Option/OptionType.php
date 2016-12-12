<?php

namespace Clab\BoardBundle\Form\Type\Option;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\BoardBundle\Form\Type\Option\OptionOptionChoiceType;

class OptionType extends AbstractType
{
    protected $proxy;
    protected $subway;

    public function __construct($proxy, $subway = false)
    {
        $this->proxy = $proxy;
        $this->subway = $subway;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data'])) {
            $option = $options['data'];
        }

        if($this->subway) {
            $builder->add('subwayType', 'choice', array(
                'required' => false,
                'choices' => array(
                    'Pain' => 'Pain',
                    'Recette' => 'Recette',
                    'Fromage' => 'Fromage',
                    'Suppl. fromage' => 'Suppl. fromage',
                    'Suppl. viandes' => 'Suppl. viandes',
                    'Toasté' => 'Toasté',
                    'Légumes' => 'Légumes',
                    'Sauce' => 'Sauce',
                ),
                'mapped' => false,
            ));
        }

        if (!$option || ($option && $option instanceof ProductOption && !$option->getParent())) {
            $builder->add('name', null, array('label' => 'pro.catalog.option.nameLabel'));
        }

        $builder
            ->add('multiple', null, array(
                'label' => 'pro.catalog.option.multipleLabel',
                'required' => false,
            ))
            ->add('required', null, array(
                'label' => 'pro.catalog.option.requiredLabel',
                'required' => false,
            ))
            ->add('minimum', null, array('label' => 'Minimum'))
            ->add('maximum', null, array('label' => 'Maximum'))
            ->add('choices', 'collection', array(
                'type' => new OptionOptionChoiceType(),
                'options'  => array(
                    'required'  => false,
                ),
                'label' => false
            ))
        ;

        if ($this->proxy instanceof Client && !$this->proxy->getForcedPricing()) {
            $builder->add('forcePrice', 'choice', array(
                'required' => true,
                'choices' => array(0 => 'Non', 1 => 'Oui'),
                'expanded' => true,
                'data' => 0,
                'mapped' => false,
                'label' => 'Certains des restaurants de votre enseigne peuvent gérer leurs prix directement. Souhaitez-vous leur appliquer tout de même cette modification ?'
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\ProductOption',
        ));
    }

    public function getName()
    {
        return 'board_option';
    }
}
