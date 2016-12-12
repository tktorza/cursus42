<?php

namespace Clab\BoardBundle\Form\Type\Option;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\RestaurantBundle\Entity\OptionChoice;

class OptionChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data'])) {
            $choice = $options['data'];
        }

        if (!$choice || ($choice && $choice instanceof OptionChoice && !$choice->getParent())) {
            $builder
                ->add('value', null, array('label' => 'pro.catalog.option.choice.nameLabel', 'required' => true))
                ->add('isOnline', null, array('label' => 'En ligne'));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\OptionChoice',
        ));
    }

    public function getName()
    {
        return 'board_option_choice';
    }
}
