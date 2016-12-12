<?php

namespace Clab\BoardBundle\Form\Type\Option;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionOptionChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('price', 'text', array(
                'label' => 'pro.catalog.option.choice.priceLabel',
                'required' => true,))
                ->addModelTransformer(
                //transforme les , dans le champs de prix par des points pour que le format soit valide
                    new CallbackTransformer(
                        function ($originalDescription) {
                            return $originalDescription;
                        },
                        function ($submittedDescription) {
                            if (!empty($submittedDescription)) {
                                return preg_replace('/[,]/', '.', $submittedDescription);
                            } else {
                                return;
                            }
                        }
                    )
                ))
            ->add('position', 'hidden')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\OptionChoice',
        ));
    }

    public function getName()
    {
        return 'board_option_option_choice';
    }
}
