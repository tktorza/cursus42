<?php

namespace Clab\BoardBundle\Form\Type\AdditionalSale;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdditionalSaleProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('price', 'text', array('required' => true))
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
                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\BoardBundle\Entity\AdditionalSaleProduct',
        ));
    }

    public function getName()
    {
        return 'board_additional_sale_product';
    }
}
