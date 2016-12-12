<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\Coupon;
use Clab\ShopBundle\Entity\OrderType;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clab\RestaurantBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestCartController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Carts",
     *      description="Get cart for a user for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getCartAction(Restaurant $restaurant)
    {
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

        return new JSONResponse($cart);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      description="Get cart for a user for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     */
    public function createAction(Request $request)
    {
        $restaurantId = $request->get('restaurantId');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $cart = new Cart();
        $cart->setRestaurant($restaurant);
        $cart->setOrderType(OrderType::ORDERTYPE_PREORDER);
        $this->getDoctrine()->getManager()->persist($cart);
        $this->getDoctrine()->getManager()->flush();

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($cart, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Add a product to a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id product"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
     */
    public function addProductToCartAction(Product $product, array $choices = array(), Request $request)
    {
        $data = $request->request->all();
        if (!empty($data)) {
            $choices = $data['choices'];
            try {
                $cart = $this->get('app_shop.cart_manager')->addProductToCartApi($product, $choices);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            try {
                $cart = $this->get('app_shop.cart_manager')->addProductToCartApi($product, $choices);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($cart, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Remove a product from a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id product"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
     */
    public function removeProductFromCartAction(Product $product)
    {
        try {
            $cart = $this->get('app_shop.cart_manager')->removeProductFromCart($product);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($cart, 'json');

        return new Response($response);
    }
    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Update a quantity from a product into the cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id product"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function updateQuantityAction(Restaurant $restaurant, $hash, $add)
    {
        try {
            $cart = $this->get('app_shop.cart_manager')->updateQuantity($restaurant, $hash, (bool) $add);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($cart, 'json');

        return new Response($response);
    }
    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Add a meal to a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id meal"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\CArt"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "id"})
     */
    public function addMealToCartAction(Meal $meal, ArrayCollection $choices)
    {
        try {
            $cart = $this->get('app_shop.cart_manager')->addMealToCart($meal, $choices);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($cart, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Empty the cart for an user for a restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *      },
     *      output="JSON reponse"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function emptyCartAction(Restaurant $restaurant)
    {
        $this->get('app_shop.cart_manager')->emptyCart($restaurant);

        new JsonResponse('Emptied', 200);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Add a coupon to a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id cart"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id coupon"}
     *      },
     *      output="JSON reponse"
     * )
     * @ParamConverter("cart", class="ClabShopBundle:Cart", options={"idCart" = "id"})
     * @ParamConverter("coupon", class="ClabShopBundle:Coupon", options={"id" = "id"})
     */
    public function addCouponAction(Cart $cart, Coupon $coupon)
    {
        try {
            $this->get('app_shop.cart_manager')->addCoupon($cart, $coupon);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        new JsonResponse('Coupon added to cart', 200);
    }

    /**
     * @ApiDoc(
     *      section="Carts",
     *      resource=true,
     *      description="Remove a coupon from a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id cart"},
     *      },
     *      output="JSON reponse"
     * )
     * @ParamConverter("cart", class="ClabShopBundle:Cart", options={"id" = "id"})
     */
    public function removeCouponAction(Cart $cart)
    {
        try {
            $this->get('app_shop.cart_manager')->removeCoupon($cart);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        new JsonResponse('Coupon removed to cart', 200);
    }
}
