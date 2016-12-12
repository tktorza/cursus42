<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use Proxies\__CG__\Clab\ShopBundle\Entity\Cart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class RestDiscountController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Discount",
     *      resource=true,
     *      description="List of discount by restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listAction(Restaurant $restaurant)
    {
        $discounts = $this->get('app_shop.discount_manager')->getAvailableDiscountsByRestaurant($restaurant);
        $view = View::create();
        $view->setData($discounts)->setStatusCode(200);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($discounts, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Discount",
     *      resource=true,
     *      description="Get the best discount for a specifi cart",
     *      requirements={
     *          {"name"="cart id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("cart", class="ClabShopBundle:Cart", options={"id" = "id"})
     */
    public function bestDiscountForCartAction(Cart $cart)
    {
        $discount = $this->get('app_shop.discount_manager')->getBestAvailableForCart($cart);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($discount, 'json');

        return new Response($response);
    }
}
