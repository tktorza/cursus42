<?php

namespace Clab\RestaurantBundle\Manager;

use Clab\BoardBundle\Entity\Client;
use Doctrine\ORM\EntityManager;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\Restaurant;

class RestaurantMenuManager
{
    protected $em;
    protected $repository;

    /**
     * @param EntityManager $em
     *                          Constructor
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu');
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return RestaurantMenu
     *                        Create a menu for a restaurant
     */
    public function createForRestaurant(Restaurant $restaurant)
    {
        $menu = new RestaurantMenu();
        $menu->setRestaurant($restaurant);

        return $menu;
    }

    /**
     * @param Client $chainStore
     *
     * @return RestaurantMenu
     *                        Create a menu for a chainstore
     */
    public function createForChainStore(Client $chainStore)
    {
        $menu = new RestaurantMenu();
        $menu->setChainStore($chainStore);

        return $menu;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return bool
     *              Initializing classic menus for a restaurant
     */
    public function initMenusForRestaurant(Restaurant $restaurant)
    {
        $menuDefault = $this->createForRestaurant($restaurant);
        $menuDefault->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT);
        $menuDefault->setName('Carte classique');
        $this->em->persist($menuDefault);

        $menuDelivery = $this->createForRestaurant($restaurant);
        $menuDelivery->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY);
        $menuDelivery->setName('Carte livraison');
        $this->em->persist($menuDelivery);

        $this->em->flush();

        return true;
    }

    /**
     * @param Client $chainStore
     *
     * @return bool
     *              Initializing classic menus for a chainstore
     */
    public function initMenusForChainStore(Client $chainStore)
    {
        $menuDefault = $this->createForChainStore($chainStore);
        $menuDefault->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT);
        $menuDefault->setName('Carte classique');
        $this->em->persist($menuDefault);

        $menuDelivery = $this->createForChainStore($chainStore);
        $menuDelivery->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY);
        $menuDelivery->setName('Carte livraison');
        $this->em->persist($menuDelivery);

        $this->em->flush();

        return true;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return mixed
     *               Get default menu for a restaurant
     */
    public function getDefaultMenuForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->getDefaultMenuForRestaurant($restaurant);
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return mixed
     *               Get a delivery menu for a restaurant
     */
    public function getDeliveryMenuForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->getDeliveryMenuForRestaurant($restaurant);
    }

    public function getByTypeForRestaurant(Restaurant $restaurant, $type)
    {
        return $this->repository->getByTypeForRestaurant($restaurant, $type);
    }
}
