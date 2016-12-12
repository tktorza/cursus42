<?php

namespace Clab\ShopBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

use Clab\ShopBundle\Entity\Cart;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Discount;

class DiscountManager
{
    protected $em;
    protected $repository;
    protected $router;

    protected $multisite = false;

    /**
     * @param EntityManager $em
     * @param Router $router
     * @param Request $request
     * @param $embeddomain
     * Constructor
     */
    public function __construct(EntityManager $em, Router $router, Request $request)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClabShopBundle:Discount');
        $this->router = $router;
        $this->request = $request;

    }

    /**
     * @param Restaurant $restaurant
     * @return array|\Clab\ShopBundle\Entity\Discount[]
     * Get discount for a given restaurant
     */
    public function getForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->findBy(array('restaurant' => $restaurant, 'isDeleted' => false), array('name' => 'asc'));
    }

    /**
     * @param Restaurant $restaurant
     * @return array
     * Get available discounts for a restaurant
     */
    public function getAvailableDiscountsByRestaurant(Restaurant $restaurant)
    {
        $discounts = $this->repository->findAllAvailable($restaurant, array('multisite' => $this->multisite));

        return $discounts;
    }

    /**
     * @param Cart $cart
     * @return null
     * Get best discount for a given cart
     */
    public function getBestAvailableForCart(Cart $cart)
    {
        $discounts = $this->getAvailableDiscountsByRestaurant($cart->getRestaurant());
        $best = null;

        foreach ($discounts as $discount) {
            $amount = $discount->getCartDiscountAmount($cart);
            if((!$best && $amount > 0) || ($best && $best->getCartDiscountAmount($cart) < $amount)) {
                $best = $discount;
            }
        }

        return $best;
    }
}
