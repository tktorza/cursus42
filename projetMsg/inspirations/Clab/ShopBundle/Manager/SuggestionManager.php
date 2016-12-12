<?php

namespace Clab\ShopBundle\Manager;

use Clab\DeliveryBundle\Entity\DeliveryCart;
use Clab\DeliveryBundle\Service\DeliveryManager;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\CartElement;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\ShopBundle\Entity\Discount;
use Clab\ShopBundle\Entity\Coupon;
use Clab\UserBundle\Entity\User;
use Clab\ShopBundle\Entity\OrderType;
use Symfony\Component\Serializer\Tests\Fixtures\VariadicConstructorArgsDummy;
use Symfony\Component\VarDumper\VarDumper;

class SuggestionManager
{
    protected $em;
    protected $router;
    protected $request;
    protected $session;
    protected $repository;
    protected $cart;
    protected $multisite = false;
    protected $categoryTab;

    /**
     * @param EntityManager   $em
     * @param Router          $router
     * @param Request         $request
     * @param $embeddomain
     * Constructor
     */
    public function __construct(EntityManager $em, Router $router, Request $request, Session $session)
    {
        $this->em = $em;
        $this->router = $router;
        $this->request = $request;

        $this->session = $session;
        $this->repository = $em->getRepository('ClabShopBundle:Cart');
    }

    public function setWeightData($restaurant)
    {
        $categories = $this->em->getRepository(ProductCategory::class)->findBy(array('restaurant' => $restaurant));

        $categoryTab = [];

        foreach ($categories as $category) {
            $categoryTab[$category->getId()] = [
                'weight' => 100,
                'categories' => $category->getParent()->getSuggestionCategoryRatios(),
            ];
        }

        $this->categoryTab = $categoryTab;

        return true;
    }

    public function chooseCartCategory($cart)
    {
        $this->setWeightData($cart->getRestaurant());

        $numbOfCategory = [];
        $total = 0;
        $elements = $cart->getElements();
        $tabReference = $this->categoryTab;


        foreach ($elements as $element) {
            if($element->getProduct() && $element->getProduct()->getCategory()) {
                $numbOfCategory[$element->getProduct()->getCategory()->getId()] = 100;
            }
        }

        foreach ($numbOfCategory as $value) {
            $total += $value;
        }

        $total /= 100;
        if($total == 0) {
            $total = 1;
        }
        $randomNumber = mt_rand(0, 100);
        $offset = 0;
        $selectedCategory = null;
        foreach ($numbOfCategory as $key => $item) {
            $numbOfCategory[$key] /= $total;
            if ($randomNumber < $numbOfCategory[$key] + $offset && $randomNumber >= $offset) {
                $selectedCategory = $key;
                break;
            }

            $offset += $numbOfCategory[$key];
        }

        if(!$selectedCategory) {
            return null;
        }

        $productReference = [];

        for ($i = 0; $i < 3; $i++) {
            $newTabReference = $tabReference[$selectedCategory]['categories'];
            $total = 0;
            $offset = 0;
            $randomNumber = mt_rand(0, 100);
            $selectedSubCategory = null;
            foreach ($newTabReference as $item) {
                $total += $item['weight'];
            }

            $total /= 100;
            if($total == 0) {
                $total = 1;
            }
            $value = 0;
            foreach ($newTabReference as $item) {
                $value = $item['weight'] / $total;
                if ($randomNumber < $value + $offset && $randomNumber >= $offset) {
                    $selectedSubCategory = intval($item['category']);
                    break;
                }
                $offset += $value;
            }

            if (!$selectedSubCategory) {
                return null;
            }

            $subCat = $this->em->getRepository(ProductCategory::class)->find($selectedSubCategory);

            $products = $subCat->getSuggestionProductsRatios();

            $total = 0;

            foreach ($products as $item) {
                $total += $item['weight'];
            }

            $total /= 100;
            $offset = 0;
            $value = 0;
            $randomNumber = mt_rand(0, 100);

            foreach ($products as $key => $product) {
                $value = $product['weight'] / $total;
                if ($randomNumber < $value + $offset && $randomNumber >= $offset && !in_array($product['product'], $productReference)) {
                    $productReference[$i] = $this->em->getRepository(Product::class)->getForRestaurantAndParent($cart->getRestaurant(), $product['product']);
                    break;
                }
                $offset += $value;
            }
        }

        return count($productReference) ? $productReference : null;
    }
}
