<?php

namespace Clab\ApiBundle\Controller;

use ArrayIterator;
use ArrayObject;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\Coupon;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\Product;

use Clab\ShopBundle\Manager\CartManager;
use Clab\ShopBundle\Manager\OrderManager;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestCartController extends FOSRestController
{
    /**
     * ### Returns the cart content ###
     *
     *      {
     *          "elements": [
     *              {
     *                  "hash": "4d264cbc545c1f779d3626d36e6fa1ca6ffd658c",
     *                  "quantity": 1,
     *                  "price": 12,
     *                  "product": {
     *                      "id": 1,
     *                      "is_online": false,
     *                      "is_online_caisse": false,
     *                      "created": "2013-02-15 00:00:00",
     *                      "name": "test",
     *                      "slug": "test",
     *                      "description": "test",
     *                      "price": 12,
     *                      "cover_small": "http://clickeat.local/app_dev.php/media/cache/resolve/square_200/files/blank.png",
     *                      "cover": "http://clickeat.local/app_dev.php/media/cache/resolve/square_400/files/blank.png",
     *                      "cover_full": "http://clickeat.local/files/blank.png"
     *                  },
     *                  "choices": [],
     *                  "childrens": []
     *              }
     *          ],
     *          "discount_amount": 0,
     *          "discount_price": 12,
     *          "coupon_amount": 0,
     *          "total_price": 12
     *      }
     *
     *
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/{restaurantId}",
     *      description="Get cart for a user for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Restaurant identifier"}
     *      },
     *      output="\Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getCartAction(Restaurant $restaurant)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->getCart($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/product/{productId}",
     *      description="Add a product to a cart",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Product identifier"},
     *      },
     *      parameters={
     *          {"name"="choices", "dataType"="collection", "required"=false, "description"="options of product"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function postCartProductAction(Product $product, Request $request)
    {
        $choices = is_string($request->get('choices')) ? json_decode($request->get('choices'), true) : $request->get('choices');
        $choices = $choices ?: array();

        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->addProductToCartApi($product, $choices);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/product/{productId}",
     *      description="Remove a product from a cart",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Product identifier"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function deleteCartProductAction(Product $product)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->removeProductFromCart($product);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/meal/{mealId}",
     *      description="Remove a meal from a cart",
     *      requirements={
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="Meal identifier"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     */
    public function deleteCartMealAction(Meal $meal)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->removeMealFromCart($meal);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/{restaurantId}/{hash}/{add}",
     *      description="Update quantity for a product of the cart",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Restaurant identifier"},
     *          {"name"="hash", "dataType"="string", "required"=true, "description"="Product hash from cart"},
     *          {"name"="add", "dataType"="boolean", "required"=true, "description"="1 to add, 0 to remove"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function putCartProductAction(Restaurant $restaurant, $hash, $add)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->updateQuantity($restaurant, $hash, (bool) $add);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * Add Meal with product and options to cart.
     * Get tags for categories searched.
     *
     * ### Parameters format with options###
     *
     *     {
     *       "product": 17575
     *       "price" : 0,
     *       "options":
     *          [
     *              6089,
     *              ...
     *          ]
     *       ...
     *     }
     *
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/meal/{mealId}",
     *      description="Add a meal to a cart",
     *      requirements={
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="Meal identifier"},
     *      },
     *      parameters={
     *          {"name"="products", "dataType"="collection", "required"=false, "description"="options of meal"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     */
    public function postCartMealAction(Meal $meal, Request $request)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $products = is_string($request->request->get('products')) ? json_decode($request->request->get('products'), true) : $request->request->get('products');

        foreach ($products as $key => &$product) {
            $product['product'] = $this->getDoctrine()->getRepository(Product::class)->find($product['product']);

            if (array_key_exists('options', $product)) {
                foreach ($product['options'] as &$option) {
                    $option = $this->getDoctrine()->getRepository(OptionChoice::class)->find($option);
                }
            }
        }

        $cart = $cartManager->addMealToCart($meal, $products);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/{restaurantId}",
     *      description="Empty the cart for an user for a restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Restaurant identifier"},
     *      },
     *      output="JSON reponse"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function deleteCartAction(Restaurant $restaurant)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cartManager->emptyCart($restaurant);

        new JsonResponse('Emptied', 200);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/coupon/{couponId}",
     *      description="Add a coupon to cart",
     *      requirements={
     *          {"name"="couponId", "dataType"="integer", "required"=true, "description"="Coupon identifier"}
     *      },
     *      output="JSON reponse"
     * )
     * @ParamConverter("coupon", class="ClabShopBundle:Coupon", options={"id" = "couponId"})
     */
    public function postCartCouponAction(Coupon $coupon)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cartManager->addCoupon($coupon);

        new JsonResponse('Coupon added to cart', 200);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/{cartId}/coupon",
     *      description="Remove a coupon from cart",
     *      requirements={
     *          {"name"="cartId", "dataType"="integer", "required"=true, "description"="Cart identifier"},
     *      },
     *      output="JSON response"
     * )
     * @ParamConverter("cart", class="ClabShopBundle:Cart", options={"id" = "cartId"})
     */
    public function deleteCartCouponAction(Cart $cart)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cartManager->removeCoupon($cart);

        new JsonResponse('Coupon removed from cart', 200);
    }

    /**
     * ### Get options for order ###
     *
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/time/{restaurantId}",
     *      description="Get time for a cart",
     *      parameters={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Restaurant identifier"}
     *      },
     *      output="JSON response"
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getTimeAction(Restaurant $restaurant)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        /**
         * @var $orderManager OrderManager
         */
        $orderManager = $this->get('app_shop.order_manager');

        $cart = $cartManager->getCart($restaurant);
        $slots = $orderManager->getSlotsForCart($cart);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource="/api/v1/carts/{cartId}/discount",
     *      description="Get the best discount for a specific cart",
     *      requirements={
     *          {"name"="cartId", "dataType"="integer", "required"=true, "description"="cart Id"}
     *      }
     * )
     *
     * @ParamConverter("cart", class="ClabShopBundle:Cart", options={"id" = "cartId"})
     */
    public function bestDiscountForCartAction(Cart $cart)
    {
        $discount = $this->get('app_shop.discount_manager')->getBestAvailableForCart($cart);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($discount, 'json');

        return new Response($response);
    }
}
