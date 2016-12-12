<?php

namespace Clab\BoardBundle\Form\Type\Meal;

use Clab\BoardBundle\Form\DataTransformer\CommaToPointTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Repository\TaxRepository;

class MealType extends AbstractType
{
    protected $proxy;
    protected $hasDelivery;
    protected $restaurantMenus = array();
    protected $commaToPointTransformer;

    public function __construct($proxy, $hasDelivery= false)
    {
        $this->proxy = $proxy;
        $this->hasDelivery = $hasDelivery;

        foreach ($proxy->getRestaurantMenus() as $menu) {
            $this->restaurantMenus[$menu->getId()] = $menu;
        }

        $this->commaToPointTransformer = new CommaToPointTransformer();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data'])) {
            $meal = $options['data'];
        }

        if (!$meal || ($meal && $meal instanceof Meal && !$meal->getParent())) {
            $builder
                ->add('name', null, array('label' => 'pro.catalog.meal.nameLabel', 'required' => true))
                ->add('description', null, array('label' => 'pro.catalog.meal.descriptionLabel'))
                ->add('tax', 'entity', array(
                    'label' => 'pro.catalog.product.taxLabel',
                    'required' => true,
                    'class' => 'ClabRestaurantBundle:Tax',
                    'query_builder' => function (TaxRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->where('t.is_online = true')
                            ->orderBy('t.rank', 'asc');
                    },
                ))
                ->add('restaurantMenus', null, array(
                    'label' => 'Carte(s)',
                    'required' => true,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $this->restaurantMenus,
                    'data' => $meal->getRestaurantMenus()->toArray(),
                ))
            ;
        }

        $builder
            ->add($builder->create('price', 'text', array('label' => 'pro.catalog.product.priceLabel'))
                ->addModelTransformer($this->commaToPointTransformer)
            )
            ->add('slots', 'collection', array(
                'type' => new MealMealSlotType(),
            ))
        ;
        if ($this->proxy->getHasCaisse() == true) {
            $builder
                ->add('taxOnSite', 'entity', array(
                'label' => 'TVA sur place',
                'required' => false,
                'class' => 'ClabRestaurantBundle:Tax',
                'query_builder' => function (TaxRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.is_online = true')
                        ->orderBy('t.rank', 'asc');
                },
                ))
                ->add($builder->create('priceOnSite', 'text', array('label' => 'pro.catalog.product.priceLabel'))
                    ->addModelTransformer($this->commaToPointTransformer)
                )
            ;
        }
        if ($this->hasDelivery) {
            $builder
                ->add('taxDelivery', 'entity', array(
                    'label' => 'pro.catalog.product.taxLabel',
                    'required' => true,
                    'class' => 'ClabRestaurantBundle:Tax',
                    'query_builder' => function (TaxRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->where('t.is_online = true')
                            ->orderBy('t.rank', 'asc');
                    },
                ))
                ->add($builder->create('deliveryPrice', 'text', array('label' => 'pro.catalog.product.priceLabel'))
                    ->addModelTransformer($this->commaToPointTransformer)
                )
            ;
        }

        if ($this->proxy instanceof Restaurant) {
            $builder
                ->add('isOnline', null, array('label' => 'pro.catalog.meal.onlineLabel', 'required' => false))
            ;
        } elseif ($this->proxy instanceof Client && !$this->proxy->getForcedPricing()) {
            $builder
                ->add('forcePrice', 'choice', array(
                    'required' => true,
                    'choices' => array(0 => 'Non', 1 => 'Oui'),
                    'expanded' => true,
                    'data' => 0,
                    'mapped' => false,
                    'label' => 'Certains des restaurants de votre enseigne peuvent gérer leurs prix directement. Souhaitez-vous leur appliquer tout de même cette modification ?',
                    )
                )
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Meal',
        ));
    }

    public function getName()
    {
        return 'board_meal';
    }
}
