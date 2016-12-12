<?php

namespace Clab\BoardBundle\Form\Type\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductOptionChoiceType extends AbstractType
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
            ->add('is_online', null, array('label' => 'Disponibilité', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\OptionChoice',
        ));
    }

    public function getName()
    {
        return 'board_product_option_choice';
    }
}
