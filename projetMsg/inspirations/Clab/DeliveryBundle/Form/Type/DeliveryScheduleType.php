<?php

namespace Clab\DeliveryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryScheduleType extends AbstractType
{
    protected $weekDays = array();

    public function __construct(array $parameters)
    {
        if (isset($parameters['weekDays']) && is_array($parameters['weekDays'])) {
            $this->weekDays = $parameters['weekDays'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('color',
                'choice',
                array(
                    'label' => 'Couleur',
                    'required' => true,
                    'choices' => array(
                        '1abc9c' => 'Turquoise',
                        '3498db' => 'Bleu',
                        '9b59b6' => 'Violet',
                        'f1c40f' => 'Jaune',
                        'e67e22' => 'Orange',
                    ),
                ))
            ->add('areas',
                'entity',
                array(
                    'class' => 'Clab\DeliveryBundle\Entity\AreaDelivery',
                    'label' => 'Zones',
                    'required' => true,
                    'property' => 'zone',
                    'multiple' => true,
                    'expanded' => false,
                ));

        if (is_array($this->weekDays) && count($this->weekDays) > 0) {
            $week = array(
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
                7 => 'Dimanche',
            );

            foreach ($week as $day => $label) {
                $builder
                    ->add('is_weekday_'.$day,
                        'checkbox',
                        array(
                            'required' => false,
                            'label' => $label,
                            'mapped' => false,
                            'data' => (count($this->weekDays[$day]) > 0) ? true : false,
                        ))
                    ->add('start_'.$day,
                        'time',
                        array(
                            'required' => false,
                            'label' => 'Heure de début',
                            'widget' => 'single_text',
                            'mapped' => false,
                            'data' => (count($this->weekDays[$day]) > 0 && isset($this->weekDays[$day][0])) ? $this->weekDays[$day][0]->getStart() : null,
                        ))
                    ->add('end_'.$day,
                        'time',
                        array(
                            'required' => false,
                            'label' => 'Heure de fin',
                            'widget' => 'single_text',
                            'mapped' => false,
                            'data' => (count($this->weekDays[$day]) > 0 && isset($this->weekDays[$day][0])) ? $this->weekDays[$day][0]->getEnd() : null,
                        ));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\DeliveryBundle\Entity\DeliverySchedule',
        ));
    }

    public function getName()
    {
        return 'clab_delivery_schedule';
    }
}
