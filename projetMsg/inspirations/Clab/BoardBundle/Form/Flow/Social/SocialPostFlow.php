<?php

namespace Clab\BoardBundle\Form\Flow\Social;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

use Clab\BoardBundle\Form\Type\Social\SocialPostType;
use Clab\BoardBundle\Form\Type\Social\SocialPostMealType;
use Clab\BoardBundle\Form\Type\Social\SocialPostProductType;
use Clab\BoardBundle\Form\Type\Social\SocialPostDiscountType;
use Clab\BoardBundle\Form\Type\Social\SocialPostEventType;

class SocialPostFlow extends FormFlow
{
    protected $em;
    protected $container;
    protected $router;

    protected $type;
    protected $restaurant = null;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }

    public function init($type, $restaurant)
    {
        $this->type = $type;
        $this->restaurant = $restaurant;
    }

    public function getName()
    {
        return 'board_social_post_flow';
    }

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'product',
                'type' => new SocialPostProductType($this->getProducts()),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return $this->type != 'product';
                },
            ),
            array(
                'label' => 'meal',
                'type' => new SocialPostMealType($this->getMeals()),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return $this->type != 'meal';
                },
            ),
            array(
                'label' => 'discount',
                'type' => new SocialPostDiscountType($this->getDiscounts()),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return $this->type != 'discount';
                },
            ),
            array(
                'label' => 'event',
                'type' => new SocialPostEventType($this->getEvents()),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return $this->type != 'event';
                },
            ),
            array(
                'label' => 'post',
                'type' => new SocialPostType(array('share_social_networks' => $this->hasShareSocialNetwork(), 'is_online' => $this->isOnline())),
            ),
        );
    }

    public function getFormOptions($step, array $options = array())
    {
        $options = parent::getFormOptions($step, $options);

        $formData = $this->getFormData();

        return $options;
    }

    public function getProducts()
    {
        $products = $this->container->get('app_restaurant.product_manager')->getForRestaurant($this->restaurant);

        if($this->type =='product' && count($products) == 0) {
            throw new \Exception('Aucun produit dans votre catalogue pour l\'instant');
        }

        return $products;
    }

    public function getMeals()
    {
        $meals = $this->container->get('app_restaurant.meal_manager')->getForRestaurant($this->restaurant);

        if($this->type =='meal' && count($meals) == 0) {
            throw new \Exception('Aucune formule dans votre catalogue pour l\'instant');
        }

        return $meals;
    }

    public function getDiscounts()
    {
        $discounts = $this->container->get('app_shop.discount_manager')->getAvailableDiscountsByRestaurant($this->restaurant);

        if($this->type =='discount' && count($discounts) == 0) {
            throw new \Exception('Vous n\'avez pas encore créé d\'offre');
        }

        return $discounts;
    }

    public function getEvents()
    {
        $foodtruck = $this->container->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->restaurant, '7 days', false);

        return $foodtruck->getPlanning();
    }

    public function hasShareSocialNetwork()
    {
        return $this->container->get('app_admin.subscription_manager')->hasAccess($this->restaurant, 'share_social_networks');
    }

    public function isOnline()
    {
        return $this->container->get('app_admin.subscription_manager')->isOnline($this->restaurant);
    }
}