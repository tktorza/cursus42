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
use Symfony\Component\VarDumper\VarDumper;

class CartManager
{
    protected $em;
    protected $router;
    protected $request;
    protected $discountManager;
    protected $deliveryManager;
    protected $session;
    protected $repository;
    protected $cart;
    protected $multisite = false;

    /**
     * @param EntityManager   $em
     * @param Router          $router
     * @param Request         $request
     * @param DiscountManager $discountManager
     * @param DeliveryManager $deliveryManager
     * @param $embeddomain
     * Constructor
     */
    public function __construct(EntityManager $em, Router $router, Request $request, DiscountManager $discountManager,  DeliveryManager $deliveryManager, Session $session)
    {
        $this->em = $em;
        $this->router = $router;
        $this->request = $request;
        $this->discountManager = $discountManager;
        $this->deliveryManager = $deliveryManager;

        $this->session = $session;
        $this->repository = $em->getRepository('ClabShopBundle:Cart');
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return string
     *                Get session name ofr a restaurant - If it's a multisite or not
     */
    public function getSessionNamespace(Restaurant $restaurant)
    {
        return 'cart_'.$restaurant->getSlug();
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return Cart|mixed
     *                    Get a cart for a given restaurant and user (in session)
     */
    public function getCart(Restaurant $restaurant)
    {
        if ($this->cart) {
            return $this->cart;
        }
        $cart = $this->session->get($this->getSessionNamespace($restaurant));
        if (!$cart) {
            $cart = new Cart();
            $cart->setRestaurant($restaurant);

            $cart->setOrderType(OrderType::ORDERTYPE_PREORDER);

            if ($this->session->get($cart->getRestaurant()->getSlug().'_delivery_address') && ( !$this->session->get('orderType') || $this->session->get('orderType') == 'delivery')) {
                $cart->setOrderType(OrderType::ORDERTYPE_DELIVERY);
            }

            $this->cart = $cart;
        }

        return $cart;
    }

    /**
     * @param Cart $cart
     *                   Update a cart
     */
    public function updateCart(Cart $cart)
    {
        $this->updateDeliveryCart($cart);
        $this->updateDiscount($cart);

        if (empty($cart->getElements())) {
            $cart = null;
        }

        $this->cart = $cart;
        $this->session->set($this->getSessionNamespace($cart->getRestaurant()), $cart);
    }

    public function clearCart(Restaurant $restaurant)
    {
        $this->session->remove($this->getSessionNamespace($restaurant));
    }

    /**
     * @param Product    $product
     * @param array|null $choices
     *
     * @return Cart|mixed
     *                    Add a product to a given cart
     */
    public function addProductToCart(Product $product, array $choices = null)
    {
        $restaurant = $product->getRestaurant();
        $cart = $this->getCart($restaurant);
        $orderType = $cart->getOrderType();

        $alreadyInCart = false;

        if (empty($choices)) {
            foreach ($cart->getElements() as $element) {
                if ($element->getProduct() && $element->getProduct()->getId() == $product->getId()) {
                    $element->setQuantity($element->getQuantity() + 1);
                    $alreadyInCart = true;
                }
            }
        }

        if (!$alreadyInCart) {
            $element = new CartElement();
            $element->setProduct($product);
            $element->setQuantity(1);
            $element->setTax($product->getTax());
            $cart->addElement($element);
        }

        if ($choices) {
            foreach ($choices as $choice) {
                $element->addChoice($choice);
            }
        }

        $element->updatePrice($orderType);

        $this->updateCart($cart);

        return $cart;
    }

    public function removeProductFromCart(Product $product)
    {
        $restaurant = $product->getRestaurant();
        $cart = $this->getCart($restaurant);
        foreach ($cart->getElements() as $element) {
            if ($element->getProduct()->getSlug() == $product->getSlug()) {
                foreach($element->getAddSaleProductCartElements() as $child_element) {
                    $cart->removeElement($child_element);
                }
                $cart->removeElement($element);
            }
        }

        $this->updateCart($cart);

        return $cart;
    }

    public function removeMealFromCart(Meal $meal)
    {
        $restaurant = $meal->getRestaurant();
        $cart = $this->getCart($restaurant);
        foreach ($cart->getElements() as $element) {
            if ($element->getMeal()) {
                if ($element->getMeal()->getSlug() == $meal->getSlug()) {
                    foreach($element->getAddSaleProductCartElements() as $child_element) {
                        $cart->removeElement($child_element);
                    }
                    $cart->removeElement($element);
                }
            }
        }

        $this->updateCart($cart);

        return $cart;
    }
    /**
     * @param Product    $product
     * @param array|null $choices
     *
     * @return Cart|mixed
     *                    Add a product to a given cart
     */
    public function addProductToCartApi(Product $product, array $choices = null)
    {
        $restaurant = $product->getRestaurant();
        $cart = $this->getCart($restaurant);
        $orderType = $cart->getOrderType();

        $alreadyInCart = false;

        if (empty($choices)) {
            foreach ($cart->getElements() as $element) {
                if ($element->getProduct() && $element->getProduct()->getId() == $product->getId()) {
                    $element->setQuantity($element->getQuantity() + 1);
                    $alreadyInCart = true;
                }
            }
        }

        if (!$alreadyInCart) {
            $element = new CartElement();
            $element->setProduct($product);
            $element->setQuantity(1);
            $element->setTax($product->getTax($orderType));
            $cart->addElement($element);
        }

        if ($choices) {
            foreach ($choices as $choice) {
                $object = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->find($choice);
                $element->addChoice($object);
            }
        }

        $element->updatePrice($orderType);

        $this->updateCart($cart);

        return $cart;
    }

    /**
     * @param Meal $meal
     * @param $slotChoices
     *
     * @return Cart|mixed
     *                    Add a meal to a given cart
     */
    public function addMealToCart(Meal $meal, $slotChoices)
    {
        $restaurant = $meal->getRestaurant();
        $cart = $this->getCart($restaurant);
        $orderType = $cart->getOrderType();

        $element = new CartElement();
        $element->setMeal($meal);
        $element->setQuantity(1);
        $element->setPrice($meal->getCurrentPrice($orderType));
        $element->setTax($meal->getTax($orderType));

        foreach ($slotChoices as $slotChoice) {
            $product = $slotChoice['product'];
            $price = $slotChoice['price'];
            $choices = $slotChoice['options'];

            $childElement = new CartElement();
            $childElement->setProduct($product);
            $childElement->setQuantity(1);
            $childElement->setPrice($price);
            $childElement->setTax($product->getTax($orderType));

            foreach ($choices as $choice) {
                $childElement->addChoice($choice);
                $childElement->setPrice($childElement->getPrice() + $choice->getPrice());
            }

            $element->addChildren($childElement);
        }

        $cart->addElement($element);

        $this->updateCart($cart);

        return $cart;
    }


    public function removeElementFromCart($elementHash,$restaurant)
    {
        $cart = $this->getCart($restaurant);
        foreach ($cart->getElements() as $element) {
            if ($element->getHash() == $elementHash) {
                foreach($element->getAddSaleProductCartElements() as $child_element) {
                    $cart->removeElement($child_element);
                }
                $cart->removeElement($element);
            }
        }

        $this->updateCart($cart);

        return $cart;
    }

    /**
     * @param Restaurant $restaurant
     * @param $hash
     * @param $add
     *
     * @return Cart|mixed
     *                    Update a quantity in a cart
     */
    public function updateQuantity(Restaurant $restaurant, $hash, $add)
    {
        $cart = $this->getCart($restaurant);

        foreach ($cart->getElements() as $element) {
            if ($hash == $element->getHash()) {
                $currentQuantity = $element->getQuantity();

                if ($add) {
                    $element->setQuantity($currentQuantity + 1);
                } else {
                    // if last, remove element
                    if ($currentQuantity == 1) {
                        $cart->removeElement($element);
                    } else {
                        $element->setQuantity($currentQuantity - 1);
                    }
                }
            }
        }

        $this->updateCart($cart);

        return $cart;
    }

    public function updateQuantityByNumber(Restaurant $restaurant, $hash, $quantity)
    {
        $cart = $this->getCart($restaurant);

        foreach ($cart->getElements() as $element) {
            if ($hash == $element->getHash()) {

                if ($quantity > 0) {
                    $element->setQuantity($quantity);
                } else {
                        $cart->removeElement($element);
                }
            }
        }

        $this->updateCart($cart);

        return $cart;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return bool
     *              Empty a cart
     */
    public function emptyCart(Restaurant $restaurant)
    {
        $this->cart = null;
        $this->session->set('cart_'.$restaurant->getSlug(), null);

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @return Cart
     *              Update the discount in a cart - Take the best discount automatically
     */
    public function updateDiscount(Cart $cart)
    {
        $discount = $this->discountManager->getBestAvailableForCart($cart);

        if ($discount) {
            $cart->setDiscount($discount);
        }

        return $cart;
    }

    /**
     * @param Cart $cart
     *
     * @return Cart
     *              Update the delivery Cart
     */
    public function updateDeliveryCart(Cart $cart)
    {
        if ($cart->getOrderType() == 3) {
            //$deliveryCart = $this->deliveryManager->getDeliveryCartForCart($cart);
            $area = $this->em->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $this->session->get('areaDelivery'),
            ));

            if($area) {
                $deliveryCart = new DeliveryCart();
                $deliveryCart->setIsOnline(true);
                $deliveryCart->setExtra($area->getPrice());
                $deliveryCart->setRestaurant($cart->getRestaurant());
                $deliveryCart->setDelay($area->getSlotLength());

                $cart->setDeliveryCart($deliveryCart);
            }
        } else {
            $cart->setDeliveryCart(null);
        }

        return $cart;
    }

    /**
     * @param Cart   $cart
     * @param Coupon $coupon
     *
     * @return bool|Cart
     *                   Add a given coupon to a given cart
     */
    public function addCoupon(Cart $cart, Coupon $coupon)
    {
        if ($coupon->isAvailableForCart($cart)) {
            $cart->setCoupon($coupon);
            $this->em->flush();

            return $cart;
        }

        return false;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     *              Remove a coupon from a cart - only one coupon can be attached to a cart
     */
    public function removeCoupon(Cart $cart)
    {
        $cart->setCoupon(null);
        $this->em->flush();

        return true;
    }

    public function setFreeSauces(Cart $cart)
    {
        $countProducts = 0;

        foreach ($cart->getElements() as $element) {
            $product = $element->getProduct();
            if ($product && $product->getCategory()) {
                $cat = $this->em->getRepository(ProductCategory::class)->find($product->getCategory()->getId());
                if("Matsuri à la carte" == $cat->getCategoryGroup()) {
                    $countProducts+=$element->getQuantity();
                }
            }
        }

        $cart->setFreeSauces(($countProducts > 0) ? round($countProducts/3) : 0);

        $this->updateCart($cart);
    }

    public function updateSauces($cart)
    {
        $restaurant = $cart->getRestaurant();
        $orderType = $cart->getOrderType() == 3 ? OrderType::ORDERTYPE_DELIVERY : OrderType::ORDERTYPE_PREORDER;


        $parentSaltySoja = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'sauce-soja-salée'));
        $parentSugarySoja = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'sauce-soja-sucrée'));
        $parentGinger = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'gingembre'));
        $parentWasabi = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'wasabi'));
        $parentFreeSaltySoja = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'sauce-soja-salée-gratuite'));
        $parentFreeSugarySoja = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'sauce-soja-sucrée-gratuite'));
        $parentFreeGinger = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'gingembre-gratuit'));
        $parentFreeWasabi = $this->em->getRepository(Product::class)->findOneBy(array('slug' => 'wasabi-gratuit'));


        $saltySoja = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentSaltySoja);
        $sugarySoja = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentSugarySoja);
        $wasabi = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentWasabi);
        $ginger = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentGinger);
        $freeSaltySoja = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentFreeSaltySoja);
        $freeSugarySoja = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentFreeSugarySoja);
        $freeWasabi = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentFreeWasabi);
        $freeGinger = $this->em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentFreeGinger);

        $hasSalt = $hasSugar = $hasFreeSalt = $hasFreeSugar = $hasWasa = $hasGinger = $hasFreeWasa = $hasFreeGinger = false;

        if (empty($choices)) {
            foreach ($cart->getElements() as $element) {
                $product = $element->getProduct();
                if($product) {
                    switch($product->getId()){
                        case $saltySoja->getId():
                            if(!$cart->getSaltySoja()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getSaltySoja());
                                $element->updatePrice($orderType);
                                $hasSalt = true;
                            }
                        break;
                        case $freeSaltySoja->getId():
                            if(!$cart->getFreeSaltySoja()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getFreeSaltySoja());
                                $element->updatePrice($orderType);
                                $hasFreeSalt = true;
                            }
                        break;
                        case $sugarySoja->getId():
                            if(!$cart->getSugarySoja()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getSugarySoja());
                                $element->updatePrice($orderType);
                                $hasSugar = true;
                            }
                            break;
                        case $freeSugarySoja->getId():
                            if(!$cart->getFreeSugarySoja()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getFreeSugarySoja());
                                $element->updatePrice($orderType);
                                $hasFreeSugar = true;
                            }
                            break;
                        case $wasabi->getId():
                            if(!$cart->getWasabi()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getWasabi());
                                $element->updatePrice($orderType);
                                $hasWasa = true;
                            }
                            break;
                        case $freeWasabi->getId():
                            if(!$cart->getFreeWasabi()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getFreeWasabi());
                                $element->updatePrice($orderType);
                                $hasFreeWasa = true;
                            }
                            break;
                        case $ginger->getId():
                            if(!$cart->getGinger()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getGinger());
                                $element->updatePrice($orderType);
                                $hasGinger = true;
                            }
                            break;
                        case $freeGinger->getId():
                            if(!$cart->getFreeGinger()){
                                $cart->removeElement($element);
                            } else {
                                $element->setQuantity($cart->getFreeGinger());
                                $element->updatePrice($orderType);
                                $hasFreeGinger = true;
                            }
                            break;
                    }
                }
            }
        }

        if (!$hasSalt && $cart->getSaltySoja()) {
            $element = new CartElement();
            $element->setProduct($saltySoja);
            $element->setQuantity($cart->getSaltySoja());
            $element->setTax($saltySoja->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasSugar && $cart->getSugarySoja()) {
            $element = new CartElement();
            $element->setProduct($sugarySoja);
            $element->setQuantity($cart->getSugarySoja());
            $element->setTax($sugarySoja->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasWasa && $cart->getWasabi()) {
            $element = new CartElement();
            $element->setProduct($wasabi);
            $element->setQuantity($cart->getWasabi());
            $element->setTax($wasabi->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasGinger && $cart->getGinger()) {
            $element = new CartElement();
            $element->setProduct($ginger);
            $element->setQuantity($cart->getGinger());
            $element->setTax($ginger->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasFreeSalt && $cart->getFreeSaltySoja()) {
            $element = new CartElement();
            $element->setProduct($freeSaltySoja);
            $element->setQuantity($cart->getFreeSaltySoja());
            $element->setTax($freeSaltySoja->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasFreeSugar && $cart->getFreeSugarySoja()) {
            $element = new CartElement();
            $element->setProduct($freeSugarySoja);
            $element->setQuantity($cart->getFreeSugarySoja());
            $element->setTax($freeSugarySoja->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasFreeWasa && $cart->getFreeWasabi()) {
            $element = new CartElement();
            $element->setProduct($freeWasabi);
            $element->setQuantity($cart->getFreeWasabi());
            $element->setTax($freeWasabi->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        if (!$hasFreeGinger && $cart->getFreeGinger()) {
            $element = new CartElement();
            $element->setProduct($freeGinger);
            $element->setQuantity($cart->getFreeGinger());
            $element->setTax($freeGinger->getTax());
            $element->updatePrice($orderType);
            $cart->addElement($element);
        }

        $this->updateCart($cart);
    }

    public function setWoodSticks($cart, $woodSticks)
    {
        if($cart->getFreeSauces() >= $woodSticks) {
            $cart->setWoodSticks($woodSticks);
        } else {
            return; 
        }
        
        $this->updateCart($cart);
    }

    public function countMeals($cart)
    {
        $count = 0;
        foreach ($cart->getElements() as $element) {
            if($element->getMeal()) {
                $count++;
            }
        }

        return $count;
    }
}
