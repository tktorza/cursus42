<?php

namespace Clab\BoardBundle\Form\Type\Product;

use Clab\BoardBundle\Form\DataTransformer\CommaToPointTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Clab\RestaurantBundle\Entity\Product;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Repository\TaxRepository;

class ProductType extends AbstractType
{
    protected $proxy;
    protected $hasDelivery;
    protected $categories;
    protected $restaurantMenus = array();
    protected $commaToPointTransformer;

    public function __construct($proxy, $hasDelivery = false)
    {
        $this->proxy = $proxy;
        $this->hasDelivery = $hasDelivery;
        $this->categories = $proxy->getProductCategories();

        foreach ($proxy->getRestaurantMenus() as $menu) {
            $this->restaurantMenus[$menu->getId()] = $menu;
        }

        $this->commaToPointTransformer = new CommaToPointTransformer();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data'])) {
            $product = $options['data'];
        }

        if (!$product || ($product && $product instanceof Product && !$product->getParent())) {

            $extraFields = $product->getExtraFields();

            $builder
                ->add('name', null, array('label' => 'pro.catalog.product.nameLabel', 'required' => true))
                ->add('description', null, array('label' => 'pro.catalog.product.descriptionLabel'))
                ->add('allergies', 'textarea', array(
                    'label' => 'Allergenes',
                    'required' => false,
                    'mapped' => false,
                    'data' => isset($extraFields['allergies']) ? $extraFields['allergies'] : ""
                    )
                )
                ->add('condiments', 'textarea', array(
                    'label' => 'Condiments inclus',
                    'required' => false,
                    'mapped' => false,
                    'data' => isset($extraFields['condiments']) ? $extraFields['condiments'] : ""
                    )
                )
                ->add('regime', null, array(
                        'label' => 'Régime',
                        'required' => false,
                        'mapped' => false,
                        'data' => isset($extraFields['regime']) ? $extraFields['regime'] : ""
                    )
                )
                ->add('nbpieces', 'text', array(
                        'label' => 'Nombre de pièces (sushis)',
                        'required' => false,
                        'mapped' => false,
                        'data' => isset($extraFields['nbpieces']) ? $extraFields['nbpieces'] : ""
                    )
                )
                ->add('calories', 'text', array(
                        'label' => 'Nombre de calories',
                        'required' => false,
                        'mapped' => false,
                        'data' => isset($extraFields['calories']) ? $extraFields['calories'] : ""
                    )
                )
                ->add('category', 'hidden', array(
                    'required' => false,
                    'label' => 'pro.catalog.product.categoryLabel',
                    'mapped' => false,
                    'data' => $product->getCategory() ? $product->getCategory()->getId() : null,
                ))
                ->add('mealOnly' , 'checkbox', array(
                    'required' =>false,
                    'label' => " "
                ))
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
                    'data' => $product->getRestaurantMenus()->toArray(),
                ))
            ;
        }
        $builder
            ->add($builder->create('price', 'text', array('label' => 'pro.catalog.product.priceLabel'))
                ->addModelTransformer($this->commaToPointTransformer)
                )
            ->add('isPDJ', 'checkbox', array('label' => 'Est-ce un plat du jour ?', 'required' => false))
            ->add('startDate', 'date', array(
                'label' => 'Date de début de validité',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))

            ->add('endDate', 'date', array(
                'label' => 'Date de fin de validité',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
            ))

            ->add('options', 'collection', array(
                'type' => new ProductOptionType(),
                'label' => false,
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
                    }
                    )
                )
                ->add($builder->create('priceOnSite', 'text', array('label' => 'pro.catalog.product.priceLabel'))
                    ->addModelTransformer($this->commaToPointTransformer)
                )
                ->add('isOnlineCaisse', null, array('required' => false, 'label' => 'En ligne sur la caisse'))
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
                ->add('isOnline', null, array('required' => false, 'label' => 'pro.catalog.product.onlineLabel'))
                ->add('editStock', 'checkbox', array('label'=> 'Est disponible?','mapped'=>false, 'required' => false))
                ->add('defaultStock', 'integer', array('label' => 'pro.catalog.product.defaultStockLabel','attr'=>array('min'=>0)))
                ->add('unlimitedStock', null, array('label' => 'pro.catalog.product.unlimitedStockLabel', 'required' => false))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Clab\RestaurantBundle\Entity\Product',
        ));
    }

    public function getName()
    {
        return 'board_product';
    }
}
