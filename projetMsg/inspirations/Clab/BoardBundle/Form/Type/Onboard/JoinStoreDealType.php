<?php

namespace Clab\BoardBundle\Form\Type\Onboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Clab\PanelBundle\Entity\Deal;

class JoinStoreDealType extends AbstractType
{
    protected $name = true;

    public function __construct(array $parameters = array())
    {
        if(isset($parameters['name'])) {
            $this->name = $parameters['name'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('discover', 'choice', array(
                'label' => 'Comment nous avez-vous découvert ?',
                'choices' => Deal::getDiscoverTypes(),
                'required' => false,
            ))
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
        return 'board_onboard_join_store_deal';
    }
}
