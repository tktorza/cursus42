<?php

namespace Clab\MultisiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\MultisiteBundle\Entity\Multisite;

class MultisiteChainType extends AbstractType
{
    protected $step = 1;

    public function __construct($step)
    {
        $this->step = $step;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->step == 1) {
            $builder
                ->add('primaryColor', null, array('label' => 'Couleur principale'))
                ->add('secondaryColor', null, array('label' => 'Couleur secondaire'))
            ;
        } elseif($this->step == 2) {
            $builder
                ->add('is_online', null, array('label' => 'En ligne', 'required' => false))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\MultisiteBundle\Entity\Multisite',
        ));
    }

    public function getName()
    {
        return 'clab_multisite_site_chain';
    }
}
