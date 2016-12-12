<?php

namespace Clab\BoardBundle\Form\Type\Appstore;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\BoardBundle\Form\DataTransformer\BooleanIntegerTransformer;

class AppDealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new BooleanIntegerTransformer();

        $builder
            ->add($builder->create('interestedInApp', 'checkbox', array('label' => 'Je suis intéressé', 'required' => false))->addModelTransformer($transformer))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\PanelBundle\Entity\Deal',
        ));
    }

    public function getName()
    {
        return 'board_app_deal';
    }
}
